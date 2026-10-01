<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Articulo;
use App\Models\ArticuloVariante;
use App\Models\DetalleVenta;
use App\Models\Local;
use App\Models\Venta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VentaController extends Controller
{
    private function getLocal(): ?Local
    {
        $user = Auth::user();
        if (!$user) return null;
        return Local::where('id_user', $user->id)->first();
    }

    /**
     * GET /api/ventas
     * Lista ventas del local, filtradas por fecha (por defecto hoy)
     */
    public function index(Request $request)
    {
        $local = $this->getLocal();
        if (!$local) {
            return response()->json(['message' => 'No tenés un local asignado.'], 403);
        }

        $query = Venta::with(['detalles.producto'])
            ->where('id_local', $local->id);

        // ── Filtros de fecha ──────────────────────────────────
        if ($request->has('from') && $request->from) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->has('to') && $request->to) {
            $query->whereDate('created_at', '<=', $request->to);
        }
        // Retrocompatibilidad: ?date= (solo un día)
        if (!$request->has('from') && !$request->has('to')) {
            if ($request->has('date') && $request->date) {
                $query->whereDate('created_at', $request->date);
            } else {
                $query->whereDate('created_at', today());
            }
        }

        // ── Filtro forma de pago ──────────────────────────
        if ($request->has('forma_pago') && $request->forma_pago) {
            $query->where('forma_de_pago', $request->forma_pago);
        }

        $query->orderByDesc('created_at');

        // ── Paginación ──────────────────────────────────
        if ($request->has('page') || $request->has('per_page')) {
            $perPage = (int) $request->input('per_page', 20);
            $paginator = $query->paginate($perPage);
            $ventas = collect($paginator->items())->map(fn($v) => $this->formatVenta($v));
            return response()->json([
                'sales'      => $ventas,
                'pagination' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page'    => $paginator->lastPage(),
                    'total'        => $paginator->total(),
                ],
            ]);
        }

        $ventas = $query->get()->map(fn($v) => $this->formatVenta($v));
        return response()->json(['sales' => $ventas]);
    }

    /**
     * POST /api/ventas
     * Crea una venta con sus detalles y descuenta stock
     */
    public function store(Request $request)
    {
        $local = $this->getLocal();
        if (!$local) {
            return response()->json(['message' => 'No tenés un local asignado.'], 403);
        }

        $request->validate([
            'items'              => 'required|array|min:1',
            'items.*.productId'  => 'required',
            'items.*.quantity'   => 'required|numeric|min:0.001',
            'items.*.price'      => 'required|numeric|min:0',
            'paymentMethod'      => 'required|string',
            'total'              => 'required|numeric|min:0',
            'clienteId'          => 'nullable|exists:personas,idpersona',
            'montoRecibido'      => 'nullable|numeric|min:0',
            'montoEfectivo'      => 'nullable|numeric|min:0',
            'montoTransferencia' => 'nullable|numeric|min:0',
            'discount'           => 'nullable|numeric|min:0|max:100',
            'surcharge'          => 'nullable|numeric|min:0|max:100',
        ]);

        $esCuenta    = $request->paymentMethod === 'cuenta';
        $esMixto     = $request->paymentMethod === 'mixto';
        $clienteId   = $request->clienteId;

        $montoEfectivo      = (float) ($request->montoEfectivo ?? 0);
        $montoTransferencia = (float) ($request->montoTransferencia ?? 0);

        if ($esMixto) {
            $totalCobrado = $montoEfectivo + $montoTransferencia;
            $pago         = min($totalCobrado, $request->total);
            $saldo        = max(0, round($request->total - $pago, 2));
            $formaPago    = 'mixto';
        } elseif ($esCuenta) {
            $montoAhora   = (float) ($request->montoRecibido ?? 0);
            $pago         = min($montoAhora, $request->total);
            $saldo        = max(0, round($request->total - $montoAhora, 2));
            $formaPago    = 'cuenta_corriente';
            $montoEfectivo = $pago;
        } else {
            $montoAhora   = (float) ($request->montoRecibido ?? $request->total);
            $pago         = min($montoAhora, $request->total);
            $saldo        = 0;
            $formaPago    = $request->paymentMethod;
            if ($formaPago === 'efectivo') {
                $montoEfectivo = $pago;
            } elseif ($formaPago === 'transferencia') {
                $montoTransferencia = $pago;
            }
        }

        // ── Pre-fetch artículos y variantes en bulk (evita N+1) ──────────
        $productIds  = collect($request->items)->pluck('productId')->unique()->values();
        $varianteIds = collect($request->items)
            ->filter(fn($i) => !empty($i['varianteId']))
            ->pluck('varianteId')->unique()->values();

        $articulos = Articulo::where('id_local', $local->id)
            ->whereIn('idarticulo', $productIds)
            ->get()
            ->keyBy('idarticulo');

        $variantes = $varianteIds->isNotEmpty()
            ? ArticuloVariante::whereIn('id_variante', $varianteIds)->get()->keyBy('id_variante')
            : collect();

        DB::beginTransaction();
        try {
            $ventaData = [
                'idcliente'     => $clienteId,
                'tipo_venta'    => 'mostrador',
                'total_venta'   => $request->total,
                'descuento'     => $request->discount ?? 0,
                'recargo'       => $request->surcharge ?? 0,
                'pago'          => $pago,
                'forma_de_pago' => $formaPago,
                'saldo'         => $saldo,
                'estado'        => 'Activo',
                'id_local'      => $local->id,
            ];

            if (\Illuminate\Support\Facades\Schema::hasColumn('ventas', 'monto_efectivo')) {
                $ventaData['monto_efectivo']      = $montoEfectivo;
                $ventaData['monto_transferencia'] = $montoTransferencia;
            }

            $venta = Venta::create($ventaData);

            foreach ($request->items as $item) {
                $articuloId = $item['productId'];
                $cantidad   = $item['quantity'];
                $varianteId = $item['varianteId'] ?? null;

                // Usar los objetos ya cargados en memoria, sin query extra
                $articulo = $articulos->get($articuloId);
                $variante = $varianteId ? $variantes->get($varianteId) : null;

                if ($variante) {
                    if ($articulo && ($articulo->tipo_venta === 'peso' || $articulo->tipo_venta === 'volumen')) {
                        $variante->stock_decimal = max(0, (float)$variante->stock_decimal - (float)$cantidad);
                    } else {
                        $variante->stock = max(0, $variante->stock - (int)$cantidad);
                    }
                    $variante->save();
                } elseif ($articulo) {
                    if ($articulo->tipo_venta === 'peso' || $articulo->tipo_venta === 'volumen') {
                        $articulo->stock_decimal = max(0, (float)$articulo->stock_decimal - (float)$cantidad);
                    } else {
                        $articulo->stock = max(0, $articulo->stock - (int)$cantidad);
                    }
                    $articulo->save();
                }

                DetalleVenta::create([
                    'idventa'              => $venta->id,
                    'idarticulo'           => $articuloId,
                    'id_variante'          => $varianteId,
                    'sku_vendido'          => $variante?->sku ?? $articulo?->codigo,
                    'descripcion_variante' => $variante?->descripcion_variante,
                    'cantidad'             => is_int($cantidad) ? $cantidad : 0,
                    'cantidad_decimal'     => !is_int($cantidad) ? $cantidad : null,
                    'precio_venta'         => $item['price'],
                    'estado'               => 'activo',
                ]);
            }

            DB::commit();

            // Respuesta mínima: el frontend no necesita el objeto completo
            return response()->json([
                'message' => 'Venta registrada exitosamente',
                'sale'    => ['id' => (string) $venta->id, 'total' => (float) $venta->total_venta],
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al registrar la venta',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * GET /api/ventas/dashboard
     * Resumen del día para el dashboard
     */
    public function dashboard()
    {
        $local = $this->getLocal();
        if (!$local) {
            return response()->json(['ventasHoy' => 0, 'stockBajo' => 0, 'pedidosPendientes' => 0]);
        }

        $ventasHoy = (float) Venta::where('id_local', $local->id)
            ->whereDate('created_at', today())
            ->sum('total_venta');

        if (in_array($local->tipo, ['mixto', 'servicio'])) {
            $serviciosHoy = (float) \App\Models\Ingreso::where('id_local', $local->id)
                ->whereDate('created_at', today())
                ->where('tipo_pago', '!=', 'cuenta_corriente')
                ->sum('monto');
            $ventasHoy += $serviciosHoy;
        }

        $stockBajo = Articulo::where('id_local', $local->id)
            ->where('tiene_variantes', false)
            ->where('stock', '>', 0)
            ->where('stock', '<=', 5)
            ->count();

        $pedidosPendientes = \App\Models\Pedido::where('id_local', $local->id)
            ->where(function ($q) {
                $q->where('tipo_pedido', '!=', 'servicio')
                  ->orWhereNull('tipo_pedido');
            })
            ->whereDoesntHave('detalles', function ($qd) {
                $qd->whereNotNull('idservicio');
            })
            ->where('estado', 'pendiente')
            ->count();

        return response()->json([
            'ventasHoy'         => (float) $ventasHoy,
            'stockBajo'         => $stockBajo,
            'pedidosPendientes' => $pedidosPendientes,
        ]);
    }

    private function formatVenta(Venta $v): array
    {
        // Cargamos relaciones faltantes de forma eficiente
        $v->loadMissing(['detalles.producto', 'detalles.variantesArticulos']);
        
        return [
            'id'            => (string) $v->id,
            'items'         => $v->detalles->map(function($d) {
                $variantName = $d->descripcion_variante ?? ($d->variantesArticulos?->descripcion_variante ?? '');
                return [
                    'productId'   => (string) $d->idarticulo,
                    'productName' => ($d->producto?->nombre ?? 'Producto eliminado') . ($variantName ? ' - ' . $variantName : ''),
                    'quantity'    => $d->cantidad_decimal ?? $d->cantidad,
                    'price'       => (float) $d->precio_venta,
                ];
            })->values()->toArray(),
            'total'         => (float) $v->total_venta,
            'paymentMethod' => $v->forma_de_pago,
            'descuento'     => (float) $v->descuento,
            'recargo'       => (float) $v->recargo,
            'pago'               => (float) $v->pago,
            'saldo'              => (float) $v->saldo,
            'montoEfectivo'      => (float) ($v->monto_efectivo ?? ($v->forma_de_pago === 'efectivo' ? $v->pago : 0)),
            'montoTransferencia' => (float) ($v->monto_transferencia ?? ($v->forma_de_pago === 'transferencia' ? $v->pago : 0)),
            'createdAt'          => $v->created_at->toISOString(),
        ];
    }
}
