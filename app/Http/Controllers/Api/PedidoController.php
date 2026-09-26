<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Articulo;
use App\Models\ArticuloVariante;
use App\Models\DetalleVenta;
use App\Models\DetallePedido;
use App\Models\Local;
use App\Models\Pedido;
use App\Models\Venta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PedidoController extends Controller
{
    private function getLocal(): ?Local
    {
        $user = Auth::user();
        if (!$user) return null;
        return Local::where('id_user', $user->id)->first();
    }

    /**
     * GET /api/pedidos
     * Lista pedidos del local con filtros de estado
     */
    public function index(Request $request)
    {
        $local = $this->getLocal();
        if (!$local) {
            return response()->json(['message' => 'No tenés un local asignado.'], 403);
        }

        $query = Pedido::with(['detalles.producto', 'detalles.variantesArticulos'])
            ->where('id_local', $local->id);

        if ($request->has('estado') && $request->estado !== 'todos') {
            $query->where('estado', $request->estado);
        }

        if ($request->has('search') && $request->search) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('nombre_cliente', 'like', "%{$s}%")
                  ->orWhere('telefono', 'like', "%{$s}%")
                  ->orWhere('id', 'like', "%{$s}%");
            });
        }

        $perPage = $request->input('per_page', 10);
        $paginator = $query->orderByDesc('created_at')->paginate($perPage);

        $pedidos = collect($paginator->items())->map(fn($p) => $this->formatPedido($p));

        return response()->json([
            'orders' => $pedidos,
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'total' => $paginator->total(),
            ]
        ]);
    }

    /**
     * POST /api/pedidos
     */
    public function store(Request $request)
    {
        $local = $this->getLocal();
        if (!$local) {
            return response()->json(['message' => 'No tenés un local asignado.'], 403);
        }

        $request->validate([
            'customerName'       => 'required|string|max:255',
            'customerPhone'      => 'nullable|string',
            'items'              => 'required|array|min:1',
            'items.*.productId'  => 'required',
            'items.*.varianteId' => 'nullable',
            'items.*.quantity'   => 'required|integer|min:1',
            'items.*.price'      => 'required|numeric|min:0',
            'total'              => 'required|numeric|min:0',
            'fechaRetiro'        => 'nullable|date',
            'horaRetiro'         => 'nullable|date_format:H:i',
        ]);

        // ── Pre-fetch en bulk para evitar N+1 ────────────────────────────
        $productIds  = collect($request->items)->pluck('productId')->unique()->values();
        $varianteIds = collect($request->items)
            ->filter(fn($i) => !empty($i['varianteId']))
            ->pluck('varianteId')->unique()->values();

        $articulos = Articulo::whereIn('idarticulo', $productIds)->get()->keyBy('idarticulo');
        $variantes = $varianteIds->isNotEmpty()
            ? ArticuloVariante::whereIn('id_variante', $varianteIds)->get()->keyBy('id_variante')
            : collect();

        DB::beginTransaction();
        try {
            $pedido = Pedido::create([
                'id_local'       => $local->id,
                'id_user'        => Auth::id(),
                'nombre_cliente' => $request->customerName,
                'telefono'       => $request->customerPhone ?? '',
                'subtotal'       => $request->total,
                'descuento'      => 0,
                'envio'          => 0,
                'total'          => $request->total,
                'estado'         => 'pendiente',
                'tipo_pedido'    => 'producto',
                'fecha_servicio' => $request->fechaRetiro,
                'hora_inicio'    => $request->horaRetiro,
            ]);

            foreach ($request->items as $item) {
                $varianteId = $item['varianteId'] ?? null;
                $variante   = $varianteId ? $variantes->get($varianteId) : null;
                $articulo   = $articulos->get($item['productId']);

                DetallePedido::create([
                    'pedido_id'            => $pedido->id,
                    'idarticulo'           => $item['productId'],
                    'id_variante'          => $varianteId,
                    'sku_vendido'          => $variante?->sku ?? $articulo?->codigo,
                    'descripcion_variante' => $variante?->descripcion_variante,
                    'cantidad'             => $item['quantity'],
                    'precio_unitario'      => $item['price'],
                    'subtotal'             => $item['price'] * $item['quantity'],
                ]);
            }

            DB::commit();

            // Respuesta mínima: el frontend no necesita el objeto completo
            return response()->json([
                'message' => 'Pedido creado exitosamente',
                'order'   => ['id' => (string) $pedido->id, 'total' => (float) $pedido->total],
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al crear pedido',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * PATCH /api/pedidos/{id}/estado
     */
    public function updateEstado(Request $request, $id)
    {
        $local  = $this->getLocal();
        $pedido = Pedido::with(['detalles.producto', 'detalles.variantesArticulos'])->where('id_local', $local?->id)->findOrFail($id);

        $request->validate([
            'status' => 'required|string|in:pendiente,en_proceso,entregado,cancelado',
        ]);

        $pedido->estado = $request->status;
        $pedido->save();

        return response()->json([
            'message' => 'Estado actualizado',
            'order'   => $this->formatPedido($pedido),
        ]);
    }

    /**
     * DELETE /api/pedidos/{id}
     * Elimina un pedido y sus detalles
     */
    public function destroy($id)
    {
        $local = $this->getLocal();
        $pedido = Pedido::where('id_local', $local?->id)->findOrFail($id);

        DB::beginTransaction();
        try {
            $pedido->detalles()->delete();
            $pedido->delete();
            DB::commit();
            return response()->json(['message' => 'Pedido eliminado exitosamente']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al eliminar pedido', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * PATCH /api/pedidos/{id}
     * Edita los datos del cliente y retiro de un pedido
     */
    public function update(Request $request, $id)
    {
        $local = $this->getLocal();
        $pedido = Pedido::with('detalles.producto')->where('id_local', $local?->id)->findOrFail($id);

        $request->validate([
            'customerName'  => 'required|string|max:255',
            'customerPhone' => 'nullable|string|max:50',
            'fechaRetiro'   => 'nullable|date',
            'horaRetiro'    => 'nullable',
        ]);

        $pedido->nombre_cliente = $request->customerName;
        $pedido->telefono       = $request->customerPhone ?? '';
        $pedido->fecha_servicio = $request->fechaRetiro;
        $pedido->hora_inicio    = $request->horaRetiro;
        $pedido->save();

        return response()->json([
            'message' => 'Pedido actualizado',
            'order'   => $this->formatPedido($pedido),
        ]);
    }

    /**
     * PUT /api/pedidos/{id}/items
     * Actualiza los items de un pedido (reemplazo completo)
     */
    public function updateItems(Request $request, $id)
    {
        $local = $this->getLocal();
        $pedido = Pedido::where('id_local', $local?->id)->findOrFail($id);

        $request->validate([
            'items'              => 'required|array|min:1',
            'items.*.productId'  => 'required',
            'items.*.varianteId' => 'nullable',
            'items.*.quantity'   => 'required|integer|min:1',
            'items.*.price'      => 'required|numeric|min:0',
            'total'              => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            // Eliminar detalles actuales
            $pedido->detalles()->delete();

            // Pre-fetch
            $productIds  = collect($request->items)->pluck('productId')->unique()->values();
            $varianteIds = collect($request->items)->filter(fn($i) => !empty($i['varianteId']))->pluck('varianteId')->unique()->values();
            $articulos = Articulo::whereIn('idarticulo', $productIds)->get()->keyBy('idarticulo');
            $variantes = $varianteIds->isNotEmpty() ? ArticuloVariante::whereIn('id_variante', $varianteIds)->get()->keyBy('id_variante') : collect();

            foreach ($request->items as $item) {
                $varianteId = $item['varianteId'] ?? null;
                $variante   = $varianteId ? $variantes->get($varianteId) : null;
                $articulo   = $articulos->get($item['productId']);

                DetallePedido::create([
                    'pedido_id'            => $pedido->id,
                    'idarticulo'           => $item['productId'],
                    'id_variante'          => $varianteId,
                    'sku_vendido'          => $variante?->sku ?? $articulo?->codigo,
                    'descripcion_variante' => $variante?->descripcion_variante,
                    'cantidad'             => $item['quantity'],
                    'precio_unitario'      => $item['price'],
                    'subtotal'             => $item['price'] * $item['quantity'],
                ]);
            }

            $pedido->total = $request->total;
            $pedido->subtotal = $request->total;
            $pedido->save();

            DB::commit();

            // Refetch para devolver formateado
            $pedido->load(['detalles.producto', 'detalles.variantesArticulos']);

            return response()->json([
                'message' => 'Items del pedido actualizados',
                'order'   => $this->formatPedido($pedido),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al actualizar items', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/pedidos/{id}/entregar
     * Marca el pedido como entregado y genera la Venta automáticamente
     */
    public function entregar(Request $request, $id)
    {
        $local  = $this->getLocal();
        $pedido = Pedido::with('detalles.producto', 'detalles.variantesArticulos')
            ->where('id_local', $local?->id)
            ->findOrFail($id);

        if ($pedido->estado === 'entregado') {
            return response()->json(['message' => 'Este pedido ya fue entregado'], 422);
        }

        $request->validate([
            'paymentMethod' => 'required|in:efectivo,transferencia,otro,cuenta_corriente',
            'clienteId'     => 'nullable|integer',
            'montoRecibido' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $total      = (float) $pedido->total;
            $esCuenta   = $request->paymentMethod === 'cuenta_corriente';
            $montoAhora = $esCuenta
                ? ($request->montoRecibido ?? 0)
                : ($request->montoRecibido ?? $total);
            $pago       = min((float) $montoAhora, $total);
            $saldo      = $esCuenta ? max(0, $total - $montoAhora) : 0;

            // Crear la Venta
            $venta = Venta::create([
                'idcliente'    => $request->clienteId,
                'tipo_venta'   => 'mostrador',
                'total_venta'  => $total,
                'descuento'    => 0,
                'recargo'      => 0,
                'pago'         => $pago,
                'forma_de_pago'=> $request->paymentMethod,
                'saldo'        => $saldo,
                'estado'       => 'Activo',
                'id_local'     => $local->id,
            ]);

            // Crear DetalleVenta + descontar stock por cada ítem del pedido
            foreach ($pedido->detalles as $detalle) {
                DetalleVenta::create([
                    'idventa'          => $venta->id,
                    'idarticulo'       => $detalle->idarticulo,
                    'id_variante'      => $detalle->id_variante,
                    'sku_vendido'      => $detalle->sku_vendido,
                    'descripcion_variante' => $detalle->descripcion_variante,
                    'cantidad'         => $detalle->cantidad,
                    'cantidad_decimal' => $detalle->cantidad_decimal,
                    'precio_venta'     => $detalle->precio_unitario,
                    'estado'           => 'Activo',
                ]);

                // Descontar stock
                if ($detalle->id_variante) {
                    $variante = ArticuloVariante::find($detalle->id_variante);
                    if ($variante) {
                        $qty = $detalle->cantidad_decimal ?? $detalle->cantidad;
                        $variante->stock_decimal = max(0, $variante->stock_decimal - $qty);
                        $variante->save();
                    }
                } elseif ($detalle->idarticulo && $detalle->producto) {
                    $articulo = $detalle->producto;
                    $qty      = $detalle->cantidad_decimal ?? $detalle->cantidad;
                    $articulo->stock = max(0, $articulo->stock - $qty);
                    $articulo->save();
                }
            }

            // Marcar pedido como entregado
            $pedido->estado = 'entregado';
            $pedido->save();

            DB::commit();

            return response()->json([
                'message' => 'Pedido entregado y venta registrada',
                'order'   => $this->formatPedido($pedido->refresh()),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error', 'error' => $e->getMessage()], 500);
        }
    }

    private function formatPedido(Pedido $p): array
    {
        return [
            'id'            => (string) $p->id,
            'customerName'  => $p->nombre_cliente,
            'customerPhone' => $p->telefono,
            'items'         => $p->detalles->map(function($d) {
                // Usa descripcion_variante persistida; si no hay, usa la relación ya eager-loaded
                $variantName = $d->descripcion_variante
                    ?? $d->variantesArticulos?->descripcion_variante
                    ?? '';
                return [
                    'productId'   => (string) $d->idarticulo,
                    'productName' => ($d->producto?->nombre ?? 'Producto eliminado') . ($variantName ? ' - ' . $variantName : ''),
                    'quantity'    => $d->cantidad_decimal ?? $d->cantidad,
                    'price'       => (float) $d->precio_unitario,
                ];
            })->values()->toArray(),
            'total'        => (float) $p->total,
            'status'       => $p->estado,
            'createdAt'    => $p->created_at?->toISOString(),
            'fechaRetiro'  => $p->fecha_servicio,
            'horaRetiro'   => $p->hora_inicio,
        ];
    }
}
