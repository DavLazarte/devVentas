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

        if ($request->has('date')) {
            $query->whereDate('created_at', $request->date);
        } else {
            $query->whereDate('created_at', today());
        }

        $ventas = $query->orderByDesc('created_at')->get()->map(fn($v) => $this->formatVenta($v));

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
            'items'             => 'required|array|min:1',
            'items.*.productId' => 'required',
            'items.*.quantity'  => 'required|numeric|min:0.001',
            'items.*.price'     => 'required|numeric|min:0',
            'paymentMethod'     => 'required|string',
            'total'             => 'required|numeric|min:0',
            'clienteId'         => 'nullable|exists:personas,idpersona',
            'montoRecibido'     => 'nullable|numeric|min:0',
        ]);

        $esCuenta    = $request->paymentMethod === 'cuenta';
        $clienteId   = $request->clienteId;
        $montoAhora  = $esCuenta ? ($request->montoRecibido ?? 0) : ($request->montoRecibido ?? $request->total);
        $pago        = min($montoAhora, $request->total);
        $saldo       = $esCuenta ? max(0, $request->total - $montoAhora) : 0;
        $formaPago   = $esCuenta ? 'cuenta_corriente' : $request->paymentMethod;

        DB::beginTransaction();
        try {
            $venta = Venta::create([
                'idcliente'     => $clienteId,
                'tipo_venta'    => 'mostrador',
                'total_venta'   => $request->total,
                'descuento'     => $request->discount ?? 0,
                'recargo'       => 0,
                'pago'          => $pago,
                'forma_de_pago' => $formaPago,
                'saldo'         => $saldo,
                'estado'        => 'Activo',
                'id_local'      => $local->id,
            ]);

            foreach ($request->items as $item) {
                $articuloId = $item['productId'];
                $cantidad   = $item['quantity'];
                $varianteId = $item['varianteId'] ?? null;

                // Descontar stock
                $articulo = Articulo::where('id_local', $local->id)->find($articuloId);
                $variante = null;

                if ($articulo) {
                    if ($varianteId) {
                        $variante = ArticuloVariante::find($varianteId);
                        if ($variante) {
                            if ($articulo->tipo_venta === 'peso' || $articulo->tipo_venta === 'volumen') {
                                $variante->stock_decimal = max(0, (float)$variante->stock_decimal - (float)$cantidad);
                            } else {
                                $variante->stock = max(0, $variante->stock - (int)$cantidad);
                            }
                            $variante->save();
                        }
                    } elseif ($articulo->tipo_venta === 'peso' || $articulo->tipo_venta === 'volumen') {
                        $articulo->stock_decimal = max(0, (float)$articulo->stock_decimal - (float)$cantidad);
                        $articulo->save();
                    } else {
                        $articulo->stock = max(0, $articulo->stock - (int)$cantidad);
                        $articulo->save();
                    }
                }

                DetalleVenta::create([
                    'idventa'          => $venta->id,
                    'idarticulo'       => $articuloId,
                    'id_variante'      => $varianteId,
                    'sku_vendido'      => $variante ? $variante->sku : ($articulo?->codigo),
                    'descripcion_variante' => $variante ? $variante->descripcion_variante : null,
                    'cantidad'         => is_int($cantidad) ? $cantidad : 0,
                    'cantidad_decimal' => !is_int($cantidad) ? $cantidad : null,
                    'precio_venta'     => $item['price'],
                    'estado'           => 'activo',
                ]);
            }

            // La deuda queda registrada en ventas.saldo
            // Cuando el cliente venga a pagar, se registrará un Ingreso desde el módulo de Clientes

            DB::commit();

            $venta->load('detalles.producto');

            return response()->json([
                'message' => 'Venta registrada exitosamente',
                'sale'    => $this->formatVenta($venta),
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

        $ventasHoy = Venta::where('id_local', $local->id)
            ->whereDate('created_at', today())
            ->sum('total_venta');

        $stockBajo = Articulo::where('id_local', $local->id)
            ->where('tiene_variantes', false)
            ->where('stock', '>', 0)
            ->where('stock', '<=', 5)
            ->count();

        $pedidosPendientes = \App\Models\Pedido::where('id_local', $local->id)
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
            'createdAt'     => $v->created_at->toISOString(),
        ];
    }
}
