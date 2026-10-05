<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CierreCaja;
use App\Models\Ingreso;
use App\Models\Local;
use App\Models\Salida;
use App\Models\Venta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CajaController extends Controller
{
    private function getLocal(): ?Local
    {
        $user = Auth::user();
        if (!$user) return null;
        return Local::where('id_user', $user->id)->first();
    }

    private function getTurnoStartTime($cajaActiva, $localId)
    {
        $inicioDia = today();
        if ($cajaActiva && $cajaActiva->fecha_apertura && $cajaActiva->fecha_apertura < today()->toDateString()) {
            return \Carbon\Carbon::parse($cajaActiva->fecha_apertura)->startOfDay();
        }
        if ($cajaActiva && $cajaActiva->created_at < $inicioDia) {
            return $cajaActiva->created_at;
        }

        // Buscar el último cierre cerrado previo a esta caja
        $ultimoCierre = CierreCaja::where(function($q) use ($localId) {
                $q->where('id_local', $localId)->orWhereNull('id_local');
            })
            ->where(function($q) {
                $q->whereIn('estado', ['cerrada', 'cerrado'])
                  ->orWhereNotNull('fecha_cierre');
            })
            ->when($cajaActiva, fn($q) => $q->where('id', '<', $cajaActiva->id))
            ->latest('id')
            ->first();

        if ($ultimoCierre && $ultimoCierre->created_at >= $inicioDia) {
            return $ultimoCierre->updated_at ?? $ultimoCierre->created_at;
        }

        return $inicioDia;
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
        
        // Determinar límites de tiempo según el turno de caja
        $cajaActiva = CierreCaja::where(function($q) use ($local) {
                $q->where('id_local', $local->id)->orWhereNull('id_local');
            })
            ->where('estado', 'abierta')
            ->latest('id')
            ->first();

        $isHoy = $date === today()->toDateString();

        $startTime = ($isHoy && $cajaActiva) ? $this->getTurnoStartTime($cajaActiva, $local->id) : null;
        $applyDateFilter = function($query) use ($date, $request, $startTime) {
            if ($request->input('scope') === 'all') return $query;
            if ($startTime) return $query->where('created_at', '>=', $startTime);
            return $query->whereDate('created_at', $date);
        };

        if ($isFinanciera) {
            $ingresosQuery = Ingreso::where('id_local', $local->id);
            $salidasQuery = Salida::where('id_local', $local->id);

            $ingresosQuery = $applyDateFilter($ingresosQuery);
            $salidasQuery = $applyDateFilter($salidasQuery);

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
        $ventasQuery = Venta::with(['detalles.producto', 'detalles.variantesArticulos', 'persona'])
            ->where('id_local', $local->id);
        $ventasQuery = $applyDateFilter($ventasQuery);
        $ventas = $ventasQuery->get();

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
                'amount'             => (float) $v->pago,
                'totalVenta'         => (float) $v->total_venta,
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

        $clasificarIngreso = function($i) {
            if (!empty($i->tipo_ingreso)) {
                if ($i->tipo_ingreso === 'servicio') {
                    return 'servicio';
                }
                if (in_array($i->tipo_ingreso, ['cobro_deuda', 'saldo_favor'])) {
                    return 'deuda';
                }
                if ($i->tipo_ingreso === 'manual' && !empty($i->idpersona)) {
                    return 'deuda';
                }
                return 'ingreso';
            }

            $descLower = strtolower($i->descripcion ?? '');
            if (str_contains($descLower, 'turno') || str_contains($descLower, 'servicio')) {
                return 'servicio';
            }
            return (!empty($i->idpersona)) ? 'deuda' : 'ingreso';
        };

        $resolverMetodoPago = function($i) {
            $descLower = strtolower(trim($i->descripcion ?? ''));
            if (($i->tipo_pago === 'efectivo' || empty($i->tipo_pago)) && (str_contains($descLower, 'transferencia') || str_contains($descLower, 'transf'))) {
                return 'transferencia';
            }
            return $i->tipo_pago ?? 'efectivo';
        };

        // Ingresos del día (cobros de deuda, cobros de turnos inmediatos, y servicios a cuenta)
        $ingresosQuery = Ingreso::with('cliente')->where('id_local', $local->id);
        $ingresosQuery = $applyDateFilter($ingresosQuery);
        $ingresosData = $ingresosQuery->get();

        $ingresos = $ingresosData->map(function($i) use ($clasificarIngreso, $resolverMetodoPago) {
            $isCuentaCorriente = $i->tipo_pago === 'cuenta_corriente';
            $category = $clasificarIngreso($i);
            $metodo = $resolverMetodoPago($i);

            $desc = $i->descripcion;
            if ($category === 'deuda') {
                $descLower = strtolower($desc ?? '');
                $clienteNombre = $i->cliente?->nombre;
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
        $salidasQuery = Salida::where('id_local', $local->id);
        $salidasQuery = $applyDateFilter($salidasQuery);
        $salidas = $salidasQuery->get()->map(function($s) {
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
        $totalServicios = (float) $ingresosCobrados->filter(function($i) use ($clasificarIngreso) {
            return $clasificarIngreso($i) === 'servicio';
        })->sum('monto');

        $totalOtrosIngresos = (float) $ingresosCobrados->filter(function($i) use ($clasificarIngreso) {
            return $clasificarIngreso($i) === 'ingreso';
        })->sum('monto');

        $ventasCuenta = (float) $ventas->where('forma_de_pago', 'cuenta_corriente')->sum('saldo');
        $serviciosCuenta = (float) $ingresosData->where('tipo_pago', 'cuenta_corriente')->filter(function($i) use ($clasificarIngreso) {
            return $clasificarIngreso($i) === 'servicio';
        })->sum('saldo');

        $resumen = [
            'efectivo'             => $efectivoVentas + $efectivoIngresos,
            'transferencia'        => $transferenciaVentas + $transferenciaIngresos,
            'cobros_deuda'         => $cobrosDeuda,
            'ventas_cuenta'        => $ventasCuenta + $serviciosCuenta,
            'total_productos'      => $totalProductos,
            'total_servicios'      => $totalServicios,
            'total_otros_ingresos' => $totalOtrosIngresos,
            'total_ingresos'       => $totalServicios + $cobrosDeuda + $totalOtrosIngresos,
            'egresos'              => (float) $salidas->sum('amount'),
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
            $ventaId = (int) substr($id, 6);
            $venta = Venta::with('detalles')->where('id_local', $local->id)->findOrFail($ventaId);

            DB::beginTransaction();
            try {
                // Revertir stock de cada detalle vendido
                foreach ($venta->detalles as $detalle) {
                    $cantidad = (float) ($detalle->cantidad_decimal ?? $detalle->cantidad ?? 0);
                    if ($cantidad <= 0) continue;

                    if ($detalle->id_variante) {
                        $variante = \App\Models\ArticuloVariante::find($detalle->id_variante);
                        if ($variante) {
                            $articulo = \App\Models\Articulo::find($detalle->idarticulo);
                            if ($articulo && in_array($articulo->tipo_venta, ['peso', 'volumen'])) {
                                $variante->stock_decimal = round((float)$variante->stock_decimal + $cantidad, 3);
                            } else {
                                $variante->stock = (int)$variante->stock + (int)$cantidad;
                            }
                            $variante->save();
                        }
                    } elseif ($detalle->idarticulo) {
                        $articulo = \App\Models\Articulo::find($detalle->idarticulo);
                        if ($articulo) {
                            if (in_array($articulo->tipo_venta, ['peso', 'volumen'])) {
                                $articulo->stock_decimal = round((float)$articulo->stock_decimal + $cantidad, 3);
                            } else {
                                $articulo->stock = (int)$articulo->stock + (int)$cantidad;
                            }
                            $articulo->save();
                        }
                    }
                }

                $venta->detalles()->delete();
                $venta->delete();

                DB::commit();
                return response()->json(['success' => true, 'message' => 'Venta eliminada y stock restituido correctamente']);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['message' => 'Error al eliminar la venta: ' . $e->getMessage()], 500);
            }
        } elseif (str_starts_with($id, 'SAL-')) {
            $salidaId = (int) substr($id, 4);
            $salida = Salida::where('id_local', $local->id)->findOrFail($salidaId);
            $salida->delete();
            return response()->json(['success' => true, 'message' => 'Egreso eliminado correctamente']);
        }

        return response()->json(['message' => 'Movimiento no encontrado.'], 404);
    }

    /**
     * GET /api/caja/estado
     * Devuelve el estado actual de la caja (abierta o cerrada)
     */
    public function estado(Request $request)
    {
        $local = $this->getLocal();
        if (!$local) {
            return response()->json(['message' => 'No tenés un local asignado.'], 403);
        }

        // Buscar caja abierta activa sin restricción de fecha para turnos cruzados
        $caja = CierreCaja::where(function($q) use ($local) {
                $q->where('id_local', $local->id)->orWhereNull('id_local');
            })
            ->where('estado', 'abierta')
            ->latest('id')
            ->first();

        if (!$caja) {
            return response()->json([
                'estado'        => 'cerrada',
                'caja'          => null,
                'ultimo_cierre' => CierreCaja::where(function($q) use ($local) {
                        $q->where('id_local', $local->id)->orWhereNull('id_local');
                    })
                    ->where(function($q) {
                        $q->whereIn('estado', ['cerrada', 'cerrado'])
                          ->orWhereNotNull('fecha_cierre');
                    })
                    ->latest('id')
                    ->first(),
            ]);
        }

        $fechaCierre = $request->input('fecha_cierre');
        if ($fechaCierre) {
            $cierreDate = \Carbon\Carbon::parse($fechaCierre);
            $startTime  = $cierreDate->copy()->startOfDay();
            $endTime    = $cierreDate->copy()->endOfDay();
        } else {
            $startTime = $this->getTurnoStartTime($caja, $local->id);
            $endTime   = null;
        }

        $ventasQuery   = Venta::where('id_local', $local->id)->where('created_at', '>=', $startTime);
        $ingresosQuery = Ingreso::where('id_local', $local->id)->where('created_at', '>=', $startTime)->where('tipo_pago', '!=', 'cuenta_corriente');
        $salidasQuery  = Salida::where('id_local', $local->id)->where('created_at', '>=', $startTime);

        if ($endTime) {
            $ventasQuery->where('created_at', '<=', $endTime);
            $ingresosQuery->where('created_at', '<=', $endTime);
            $salidasQuery->where('created_at', '<=', $endTime);
        }

        $ventas   = $ventasQuery->get();
        $ingresos = $ingresosQuery->get();
        $salidas  = $salidasQuery->get();

        $efectivoVentas = (float) $ventas->sum(fn($v) =>
            $v->forma_de_pago === 'mixto' ? (float)($v->monto_efectivo ?? 0) :
            ($v->forma_de_pago === 'efectivo' ? (float)$v->pago : 0)
        );
        $efectivoIngresos     = (float) $ingresos->where('tipo_pago', 'efectivo')->sum('monto');
        $transferenciaVentas  = (float) $ventas->sum(fn($v) =>
            $v->forma_de_pago === 'mixto' ? (float)($v->monto_transferencia ?? 0) :
            ($v->forma_de_pago === 'transferencia' ? (float)$v->pago : 0)
        );
        $transferenciaIngresos = (float) $ingresos->where('tipo_pago', 'transferencia')->sum('monto');
        $totalEgresos          = (float) $salidas->sum('monto');
        $efectivoTotal         = $efectivoVentas + $efectivoIngresos;
        $transferenciaTotal    = $transferenciaVentas + $transferenciaIngresos;

        $totalVentasProductos   = (float) $ventas->sum('pago');
        $totalServiciosIngresos = (float) $ingresos->sum('monto');
        $totalVentasCuenta      = (float) $ventas->where('forma_de_pago', 'cuenta_corriente')->sum('saldo');

        return response()->json([
            'estado' => 'abierta',
            'caja'   => [
                'id'                           => $caja->id,
                'fecha_apertura'               => $caja->fecha_apertura,
                'monto_apertura'               => (float) $caja->monto_apertura,
                'monto_apertura_transferencia' => (float) $caja->monto_apertura_transferencia,
                'total_ventas_productos'       => $totalVentasProductos,
                'ventas_efectivo'              => $efectivoVentas,
                'ventas_transferencia'         => $transferenciaVentas,
                'ventas_cuenta'                => $totalVentasCuenta,
                'total_servicios_ingresos'     => $totalServiciosIngresos,
                'servicios_efectivo'           => $efectivoIngresos,
                'servicios_transferencia'      => $transferenciaIngresos,
                'efectivo'                     => $efectivoTotal,
                'transferencia'                => $transferenciaTotal,
                'egresos'                      => $totalEgresos,
                'efectivo_en_caja'             => round((float)$caja->monto_apertura + $efectivoTotal - $totalEgresos, 2),
                'transferencia_en_caja'        => round((float)$caja->monto_apertura_transferencia + $transferenciaTotal, 2),
            ],
        ]);
    }

    /**
     * POST /api/caja/abrir
     * Abre un nuevo turno de caja
     */
    public function abrir(Request $request)
    {
        $local = $this->getLocal();
        if (!$local) {
            return response()->json(['message' => 'No tenés un local asignado.'], 403);
        }

        $request->validate([
            'monto_apertura'               => 'required|numeric|min:0',
            'monto_apertura_transferencia' => 'nullable|numeric|min:0',
        ]);

        // Verificar ÚNICAMENTE si ya hay una caja abierta en curso (permite múltiples aperturas y cierres en el mismo día)
        $cajaAbierta = CierreCaja::where(function($q) use ($local) {
                $q->where('id_local', $local->id)->orWhereNull('id_local');
            })
            ->where('estado', 'abierta')
            ->first();

        if ($cajaAbierta) {
            return response()->json(['message' => 'Ya tenés una caja abierta en curso. Debés cerrarla antes de iniciar un nuevo turno.'], 422);
        }

        $fechaApertura = $request->input('fecha_apertura', today()->toDateString());
        $createdAt = ($fechaApertura === today()->toDateString()) ? now() : \Carbon\Carbon::parse($fechaApertura)->startOfDay();

        $caja = CierreCaja::create([
            'id_local'                     => $local->id,
            'fecha_apertura'               => $fechaApertura,
            'monto_apertura'               => $request->monto_apertura,
            'monto_apertura_transferencia' => $request->monto_apertura_transferencia ?? 0,
            'estado'                       => 'abierta',
            'created_at'                   => $createdAt,
            'updated_at'                   => $createdAt,
        ]);

        return response()->json(['message' => 'Caja abierta correctamente', 'caja' => $caja], 201);
    }

    /**
     * POST /api/caja/cerrar
     * Cierra el turno de caja actual guardando el arqueo real
     */
    public function cerrar(Request $request)
    {
        $local = $this->getLocal();
        if (!$local) {
            return response()->json(['message' => 'No tenés un local asignado.'], 403);
        }

        $request->validate([
            'monto_efectivo_real'      => 'required|numeric|min:0',
            'monto_transferencia_real' => 'nullable|numeric|min:0',
            'monto_cuenta_real'        => 'nullable|numeric|min:0',
        ]);

        // Buscar la caja abierta activa más reciente
        $caja = CierreCaja::where(function($q) use ($local) {
                $q->where('id_local', $local->id)->orWhereNull('id_local');
            })
            ->where('estado', 'abierta')
            ->latest('id')
            ->first();

        if (!$caja) {
            return response()->json(['message' => 'No hay una caja abierta actualmente para cerrar.'], 422);
        }

        $fechaCierre = $request->input('fecha_cierre', today()->toDateString());
        if ($fechaCierre !== today()->toDateString() && $caja->fecha_apertura !== $fechaCierre) {
            $cierreDate = \Carbon\Carbon::parse($fechaCierre);
            $startTime  = $cierreDate->copy()->startOfDay();
            $endTime    = $cierreDate->copy()->endOfDay();
        } else {
            $startTime = $this->getTurnoStartTime($caja, $local->id);
            $endTime   = \Carbon\Carbon::parse($fechaCierre)->endOfDay();
        }

        $ventas   = Venta::where('id_local', $local->id)->where('created_at', '>=', $startTime)->where('created_at', '<=', $endTime)->get();
        $ingresos = Ingreso::where('id_local', $local->id)->where('created_at', '>=', $startTime)->where('created_at', '<=', $endTime)->get();
        $salidas  = Salida::where('id_local', $local->id)->where('created_at', '>=', $startTime)->where('created_at', '<=', $endTime)->get();

        $ventasEfectivo = (float) $ventas->sum(fn($v) =>
            $v->forma_de_pago === 'mixto' ? (float)($v->monto_efectivo ?? 0) :
            ($v->forma_de_pago === 'efectivo' ? (float)$v->pago : 0)
        );
        $ventasTransferencia = (float) $ventas->sum(fn($v) =>
            $v->forma_de_pago === 'mixto' ? (float)($v->monto_transferencia ?? 0) :
            ($v->forma_de_pago === 'transferencia' ? (float)$v->pago : 0)
        );
        $ventasCuenta  = (float) $ventas->where('forma_de_pago', 'cuenta_corriente')->sum('saldo');
        $totalVentas   = (float) $ventas->sum('pago');
        $totalIngresos = (float) $ingresos->where('tipo_pago', '!=', 'cuenta_corriente')->sum('monto');
        
        $ingresosTransferencia = (float) $ingresos->where('tipo_pago', 'transferencia')->sum('monto');
        $totalSalidas  = (float) $salidas->sum('monto');

        // Sistema espera esto en físico y en banco para este turno
        $efectivoSistema = round((float)$caja->monto_apertura + $ventasEfectivo + $totalIngresos - $ingresosTransferencia - $totalSalidas, 2);
        $transferenciaSistema = round((float)$caja->monto_apertura_transferencia + $ventasTransferencia + $ingresosTransferencia, 2);

        // Montos reales ingresados manualmente por el operador
        $montoEfectivoReal      = (float) $request->monto_efectivo_real;
        $montoTransferenciaReal = (float) ($request->monto_transferencia_real ?? $transferenciaSistema);
        $montoCuentaReal        = (float) ($request->monto_cuenta_real ?? $ventasCuenta);
        $montoTotalReal         = round($montoEfectivoReal + $montoTransferenciaReal + $montoCuentaReal, 2);

        $fechaCierre = $request->input('fecha_cierre', today()->toDateString());
        $caja->update([
            'fecha_cierre'                    => $fechaCierre,
            'monto_total_ventas'              => $totalVentas,
            'ventas_efectivo'                 => $ventasEfectivo,
            'ventas_transferencia'            => $ventasTransferencia,
            'ventas_cuenta'                   => $ventasCuenta,
            'ingresos'                        => $totalIngresos,
            'salidas'                         => $totalSalidas,
            'monto_cierre'                    => $efectivoSistema,
            'monto_cierre_real'               => $montoEfectivoReal,
            'monto_cierre_transferencia_real' => $montoTransferenciaReal,
            'monto_cierre_cuenta_real'        => $montoCuentaReal,
            'estado'                          => 'cerrada',
        ]);

        return response()->json([
            'message'                   => 'Caja cerrada correctamente',
            'diferencia_efectivo'       => round($montoEfectivoReal - $efectivoSistema, 2),
            'efectivo_sistema'          => $efectivoSistema,
            'monto_efectivo_real'       => $montoEfectivoReal,
            'monto_transferencia_real'  => $montoTransferenciaReal,
            'monto_cuenta_real'         => $montoCuentaReal,
            'total_real'                => $montoTotalReal,
            'caja'                      => $caja->fresh(),
        ]);
    }

    /**
     * GET /api/caja/cierres
     * Devuelve el historial de cierres de caja (cajas cerradas) del local.
     */
    public function cierres()
    {
        $local = $this->getLocal();
        if (!$local) {
            return response()->json(['message' => 'No tenés un local asignado.'], 403);
        }

        // Devolver todos los cierres históricos disponibles del local
        $cierres = CierreCaja::where(function($q) use ($local) {
                $q->where('id_local', $local->id)->orWhereNull('id_local');
            })
            ->where(function($q) {
                $q->whereIn('estado', ['cerrada', 'cerrado'])
                  ->orWhereNotNull('fecha_cierre');
            })
            ->orderBy('id', 'desc')
            ->limit(100)
            ->get();

        return response()->json(['cierres' => $cierres]);
    }
}
