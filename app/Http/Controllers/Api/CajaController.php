<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ingreso;
use App\Models\Local;
use App\Models\Salida;
use App\Models\Venta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CajaController extends Controller
{
    private function getLocal(): ?Local
    {
        $user = Auth::user();
        if (!$user) return null;
        return Local::where('id_user', $user->id)->first();
    }

    /**
     * GET /api/caja
     * Devuelve ingresos y egresos del día (o la fecha pedida)
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $local = $this->getLocal();
        if (!$local) {
            return response()->json(['message' => 'No tenés un local asignado.'], 403);
        }

        $date = $request->has('date') ? $request->date : today()->toDateString();
        $isFinanciera = str_starts_with($user->role->name ?? '', 'financiera');

        if ($isFinanciera) {
            $ingresosQuery = Ingreso::where('id_local', $local->id);
            $salidasQuery = Salida::where('id_local', $local->id);

            // Si no pide 'all', filtramos por fecha
            if ($request->input('scope') !== 'all') {
                $ingresosQuery->whereDate('created_at', $date);
                $salidasQuery->whereDate('created_at', $date);
            }

            $ingresosData = $ingresosQuery->get();
            $salidasData = $salidasQuery->get();

            $ingresos = $ingresosData->map(fn($i) => [
                'id'            => 'ING-' . $i->id_ingreso,
                'type'          => 'ingreso',
                'amount'        => (float) $i->monto,
                'description'   => $i->descripcion ?? 'Ingreso',
                'paymentMethod' => $i->tipo_pago ?? 'efectivo',
                'createdAt'     => $i->created_at?->toISOString(),
                'saleId'        => null,
                'items'         => [],
            ]);

            $salidas = $salidasData->map(fn($s) => [
                'id'            => 'SAL-' . $s->idsalida,
                'type'          => 'egreso',
                'amount'        => (float) $s->monto,
                'description'   => $s->descripcion ?? 'Egreso',
                'paymentMethod' => null,
                'createdAt'     => $s->created_at?->toISOString(),
                'saleId'        => null,
                'items'         => [],
            ]);

            // Resumen acumulado (Caja General)
            $totalIngresos = Ingreso::where('id_local', $local->id)->sum('monto');
            $totalSalidas = Salida::where('id_local', $local->id)->sum('monto');
            $cajaGeneral = (float) $totalIngresos - (float) $totalSalidas;

            $resumen = [
                'efectivo'      => (float) $ingresosData->where('tipo_pago', 'efectivo')->sum('monto') + (float) $ingresosData->whereNull('tipo_pago')->sum('monto'),
                'transferencia' => (float) $ingresosData->where('tipo_pago', 'transferencia')->sum('monto'),
                'cobros_deuda'  => 0,
                'egresos'       => (float) $salidasData->sum('monto'),
                'caja_general'  => $cajaGeneral
            ];

            $all = $ingresos->concat($salidas)->sortByDesc('createdAt')->values();
            return response()->json(['cashflow' => $all, 'resumen' => $resumen]);
        }

        // Ventas del día como ingresos automáticos (Para Pos Normal)
        $ventas = Venta::with(['detalles.producto', 'detalles.variantesArticulos', 'persona'])
            ->where('id_local', $local->id)
            ->whereDate('created_at', $date)
            ->get();

        $ventasEntries = $ventas->map(function($v) {
            $clienteNombre = $v->persona?->nombre;
            $desc = 'Venta #' . $v->id;
            if ($clienteNombre) {
                $desc .= ' - ' . $clienteNombre;
            }
            if ($v->forma_de_pago === 'cuenta_corriente') {
                $desc .= ' (A cuenta)';
            }

            $montoEf = (float) ($v->monto_efectivo ?? ($v->forma_de_pago === 'efectivo' ? $v->pago : 0));
            $montoTr = (float) ($v->monto_transferencia ?? ($v->forma_de_pago === 'transferencia' ? $v->pago : 0));

            return [
                'id'                 => 'VENTA-' . $v->id,
                'type'               => 'ingreso',
                'category'           => 'producto',
                'amount'             => (float) $v->pago,       // lo que se cobró (para el balance de caja)
                'totalVenta'         => (float) $v->total_venta, // total real de la venta
                'pago'               => (float) $v->pago,
                'saldo'              => (float) $v->saldo,
                'montoEfectivo'      => $montoEf,
                'montoTransferencia' => $montoTr,
                'description'        => $desc,
                'paymentMethod'      => $v->forma_de_pago,
                'createdAt'          => $v->created_at->toISOString(),
                'saleId'             => (string) $v->id,
                'items'              => $v->detalles->map(function($d) {
                    $variantName = $d->descripcion_variante ?? ($d->variantesArticulos?->descripcion_variante ?? '');
                    return [
                        'productName' => ($d->producto?->nombre ?? 'Servicio / Producto') . ($variantName ? ' - ' . $variantName : ''),
                        'quantity'    => $d->cantidad_decimal ?? $d->cantidad ?? 1,
                        'price'       => (float) $d->precio_venta,
                    ];
                })->values()->toArray(),
            ];
        });

        $isLocalServicioOrMixto = in_array($local->tipo, ['servicio', 'mixto']);

        // Clasificador relacional directo por columna tipo_ingreso
        $clasificarIngreso = function($i) use ($isLocalServicioOrMixto) {
            // Relacional: si tiene tipo_ingreso definido en la base de datos
            if (!empty($i->tipo_ingreso)) {
                if ($i->tipo_ingreso === 'servicio') {
                    return $isLocalServicioOrMixto ? 'servicio' : 'deuda';
                }
                if (in_array($i->tipo_ingreso, ['cobro_deuda', 'saldo_favor'])) {
                    return 'deuda';
                }
                if ($i->tipo_ingreso === 'manual' && !empty($i->idpersona)) {
                    return 'deuda';
                }
                return 'ingreso';
            }

            // Fallback de retrocompatibilidad
            if ($isLocalServicioOrMixto && str_contains(strtolower($i->descripcion ?? ''), 'turno')) {
                return 'servicio';
            }
            return (!empty($i->idpersona)) ? 'deuda' : 'ingreso';
        };

        // Resolución inteligente de la forma de pago (si por defecto quedó en efectivo pero la descripción dice transferencia)
        $resolverMetodoPago = function($i) {
            $descLower = strtolower(trim($i->descripcion ?? ''));
            if (($i->tipo_pago === 'efectivo' || empty($i->tipo_pago)) && (str_contains($descLower, 'transferencia') || str_contains($descLower, 'transf'))) {
                return 'transferencia';
            }
            return $i->tipo_pago ?? 'efectivo';
        };

        // Ingresos del día (cobros de deuda, cobros de turnos inmediatos, y servicios a cuenta)
        $ingresosData = Ingreso::with('cliente')
            ->where('id_local', $local->id)
            ->whereDate('created_at', $date)
            ->get();

        $ingresos = $ingresosData->map(function($i) use ($clasificarIngreso, $resolverMetodoPago) {
            $isCuentaCorriente = $i->tipo_pago === 'cuenta_corriente';
            $category = $clasificarIngreso($i);
            $metodo = $resolverMetodoPago($i);

            $desc = $i->descripcion;
            if ($category === 'deuda') {
                $descLower = strtolower($desc ?? '');
                $clienteNombre = $i->cliente?->nombre;
                // Si la descripción previa era solo "transferencia" o similar, enriquecerla para mayor claridad
                if (!str_contains($descLower, 'pago') && !str_contains($descLower, 'cobro') && !str_contains($descLower, 'deuda') && !str_contains($descLower, 'saldo')) {
                    $desc = 'Pago de deuda' . ($desc ? " ({$desc})" : '') . ($clienteNombre ? " - {$clienteNombre}" : '');
                } elseif ($clienteNombre && !str_contains($descLower, strtolower($clienteNombre))) {
                    $desc .= " - {$clienteNombre}";
                }
            }

            return [
                'id'            => 'ING-' . $i->id_ingreso,
                'type'          => 'ingreso',
                'category'      => $category,
                'amount'        => $isCuentaCorriente ? 0 : (float) $i->monto,
                'totalVenta'    => (float) $i->monto,
                'saldo'         => (float) $i->saldo,
                'description'   => $desc ?? ($isCuentaCorriente ? 'Servicio a cuenta' : 'Ingreso manual'),
                'paymentMethod' => $metodo,
                'createdAt'     => $i->created_at?->toISOString(),
                'saleId'        => null,
                'items'         => [],
            ];
        });

        // Egresos del día
        $salidas = Salida::where('id_local', $local->id)
            ->whereDate('created_at', $date)
            ->get()
            ->map(function($s) {
                $isComision = $s->tipo_salida === 'comision' || str_contains(strtolower($s->descripcion ?? ''), 'comisi');
                return [
                    'id'            => 'SAL-' . $s->idsalida,
                    'type'          => 'egreso',
                    'category'      => $isComision ? 'comision' : 'egreso',
                    'amount'        => (float) $s->monto,
                    'description'   => $s->descripcion ?? 'Egreso',
                    'paymentMethod' => null,
                    'createdAt'     => $s->created_at?->toISOString(),
                    'saleId'        => null,
                    'items'         => [],
                ];
            });

        // Resumen por forma de pago (sumando ventas + ingresos directos)
        $ingresosCobrados = $ingresosData->where('tipo_pago', '!=', 'cuenta_corriente');
        
        $efectivoVentas = (float) $ventas->sum(function($v) {
            if ($v->forma_de_pago === 'mixto') {
                return (float) ($v->monto_efectivo ?? 0);
            }
            return $v->forma_de_pago === 'efectivo' ? (float) $v->pago : 0;
        });
        $efectivoIngresos = (float) $ingresosCobrados->filter(function($i) use ($resolverMetodoPago) {
            return in_array($resolverMetodoPago($i), ['efectivo', null]);
        })->sum('monto');
        
        $transferenciaVentas = (float) $ventas->sum(function($v) {
            if ($v->forma_de_pago === 'mixto') {
                return (float) ($v->monto_transferencia ?? 0);
            }
            return $v->forma_de_pago === 'transferencia' ? (float) $v->pago : 0;
        });
        $transferenciaIngresos = (float) $ingresosCobrados->filter(function($i) use ($resolverMetodoPago) {
            return $resolverMetodoPago($i) === 'transferencia';
        })->sum('monto');
        
        $cobrosDeuda = (float) $ingresosCobrados->filter(function($i) use ($clasificarIngreso) {
            return $clasificarIngreso($i) === 'deuda';
        })->sum('monto');

        $totalProductos = (float) $ventas->sum('pago');
        $totalServicios = $isLocalServicioOrMixto
            ? (float) $ingresosCobrados->filter(function($i) use ($clasificarIngreso) {
                return $clasificarIngreso($i) === 'servicio';
            })->sum('monto')
            : 0;

        $ventasCuenta = (float) $ventas->where('forma_de_pago', 'cuenta_corriente')->sum('saldo');
        $serviciosCuenta = $isLocalServicioOrMixto
            ? (float) $ingresosData->where('tipo_pago', 'cuenta_corriente')->filter(function($i) use ($clasificarIngreso) {
                return $clasificarIngreso($i) === 'servicio';
            })->sum('saldo')
            : 0;

        $resumen = [
            'efectivo'        => $efectivoVentas + $efectivoIngresos,
            'transferencia'   => $transferenciaVentas + $transferenciaIngresos,
            'cobros_deuda'    => $cobrosDeuda,
            'ventas_cuenta'   => $ventasCuenta + $serviciosCuenta,
            'total_productos' => $totalProductos,
            'total_servicios' => $totalServicios,
            'egresos'         => (float) Salida::where('id_local', $local->id)->whereDate('created_at', $date)->sum('monto'),
        ];

        $all = $ventasEntries->concat($ingresos)->concat($salidas)
            ->sortByDesc('createdAt')
            ->values();

        return response()->json(['cashflow' => $all, 'resumen' => $resumen]);
    }

    /**
     * POST /api/caja
     * Registrar movimiento manual de caja (ingreso o egreso)
     */
    public function store(Request $request)
    {
        $local = $this->getLocal();
        if (!$local) {
            return response()->json(['message' => 'No tenés un local asignado.'], 403);
        }

        $request->validate([
            'type'        => 'required|in:ingreso,egreso',
            'amount'      => 'required|numeric|min:0.01',
            'description' => 'required|string|max:255',
        ]);

        if ($request->type === 'ingreso') {
            $isCuentaCorriente = ($request->tipo_pago === 'cuenta_corriente');
            $entry = Ingreso::create([
                'idpersona'    => $request->idpersona,
                'monto'        => $request->amount,
                'tipo_pago'    => $request->tipo_pago ?? 'efectivo',
                'tipo_ingreso' => $request->input('tipo_ingreso', 'manual'),
                'descripcion'  => $request->description,
                'saldo'        => $isCuentaCorriente ? $request->amount : 0,
                'estado'       => 'activo',
                'id_local'     => $local->id,
            ]);
            $id = 'ING-' . $entry->id_ingreso;
        } else {
            $entry = Salida::create([
                'idpersona'   => $request->idpersona,
                'tipo_salida' => $request->input('tipo_salida', 'gasto'),
                'monto'       => $request->amount,
                'descripcion' => $request->description,
                'saldo'       => $request->amount,
                'estado'      => 'activo',
                'id_local'    => $local->id,
            ]);
            $id = 'SAL-' . $entry->idsalida;
        }

        return response()->json([
            'message'  => 'Movimiento registrado',
            'cashflow' => [
                'id'          => $id,
                'type'        => $request->type,
                'amount'      => (float) $request->amount,
                'description' => $request->description,
                'createdAt'   => $entry->created_at->toISOString(),
                'saleId'      => null,
            ],
        ], 201);
    }

    /**
     * PUT /api/caja/movimientos/{id}
     * Edita un movimiento de caja (Ingreso, Venta o Egreso)
     */
    public function updateMovimiento($id, Request $request)
    {
        $local = $this->getLocal();
        if (!$local) {
            return response()->json(['message' => 'No tenés un local asignado.'], 403);
        }

        $request->validate([
            'amount'        => 'required|numeric|min:0.01',
            'description'   => 'nullable|string|max:255',
            'paymentMethod' => 'nullable|string|in:efectivo,transferencia,cuenta_corriente,mixto,otro',
        ]);

        $monto = (float) $request->amount;
        $desc = $request->description;
        $metodo = $request->paymentMethod ?? 'efectivo';

        if (str_starts_with($id, 'ING-')) {
            $ingresoId = (int) substr($id, 4);
            $ingreso = Ingreso::where('id_local', $local->id)->findOrFail($ingresoId);

            $ingreso->monto = $monto;
            if ($desc !== null) {
                $ingreso->descripcion = $desc;
            }
            $ingreso->tipo_pago = $metodo;

            if ($metodo === 'cuenta_corriente') {
                $ingreso->saldo = $monto;
            } elseif ($ingreso->getOriginal('tipo_pago') === 'cuenta_corriente') {
                $ingreso->saldo = 0;
            }

            $ingreso->save();

            return response()->json(['success' => true, 'message' => 'Ingreso actualizado correctamente']);
        } elseif (str_starts_with($id, 'VENTA-')) {
            $ventaId = (int) substr($id, 6);
            $venta = Venta::where('id_local', $local->id)->findOrFail($ventaId);

            $venta->forma_de_pago = $metodo;
            if ($metodo === 'cuenta_corriente') {
                $venta->pago = 0;
                $venta->saldo = $venta->total_venta;
            } else {
                $venta->pago = $monto;
                $venta->saldo = max(0, round($venta->total_venta - $monto, 2));
            }
            $venta->save();

            return response()->json(['success' => true, 'message' => 'Venta actualizada correctamente']);
        } elseif (str_starts_with($id, 'SAL-')) {
            $salidaId = (int) substr($id, 4);
            $salida = Salida::where('id_local', $local->id)->findOrFail($salidaId);

            $salida->monto = $monto;
            if ($desc !== null) {
                $salida->descripcion = $desc;
            }
            $salida->save();

            return response()->json(['success' => true, 'message' => 'Egreso actualizado correctamente']);
        }

        return response()->json(['message' => 'Tipo de movimiento no válido'], 400);
    }
    /**
     * DELETE /api/caja/movimientos/{id}
     * Elimina un movimiento de caja (Ingreso o Egreso)
     */
    public function destroyMovimiento($id)
    {
        $local = $this->getLocal();
        if (!$local) {
            return response()->json(['message' => 'No tenés un local asignado.'], 403);
        }

        if (str_starts_with($id, 'ING-')) {
            $ingresoId = (int) substr($id, 4);
            $ingreso = Ingreso::where('id_local', $local->id)->findOrFail($ingresoId);
            
            // Revertir en el cliente si el ingreso estaba asignado a una persona
            if ($ingreso->idpersona) {
                $persona = $ingreso->cliente ?? \App\Models\Persona::find($ingreso->idpersona);
                if ($persona) {
                    $desc = mb_strtolower($ingreso->descripcion ?? '');
                    if (str_contains($desc, 'favor') || str_contains($desc, 'saldo') || str_contains($desc, 'carga')) {
                        $persona->saldo_favor = max(0, round(($persona->saldo_favor ?? 0) - (float)$ingreso->monto, 2));
                        $persona->save();
                    }
                }
            }

            $ingreso->delete();
            return response()->json(['success' => true, 'message' => 'Ingreso eliminado correctamente']);
        } elseif (str_starts_with($id, 'VENTA-')) {
            return response()->json(['message' => 'Las ventas se deben eliminar desde el panel de Ventas.'], 400);
        } elseif (str_starts_with($id, 'SAL-')) {
            $salidaId = (int) substr($id, 4);
            $salida = Salida::where('id_local', $local->id)->findOrFail($salidaId);
            $salida->delete();
            return response()->json(['success' => true, 'message' => 'Egreso eliminado correctamente']);
        }

        return response()->json(['message' => 'Movimiento no encontrado.'], 404);
    }
}
