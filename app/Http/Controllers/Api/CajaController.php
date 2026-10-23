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
        if (!$cajaActiva) {
            return today();
        }

        // El turno de caja comienza estrictamente en la fecha y hora en que fue abierto
        if ($cajaActiva->fecha_apertura && (!$cajaActiva->created_at || \Carbon\Carbon::parse($cajaActiva->fecha_apertura)->startOfDay()->lt(\Carbon\Carbon::parse($cajaActiva->created_at)->startOfDay()))) { return \Carbon\Carbon::parse($cajaActiva->fecha_apertura)->startOfDay(); } if ($cajaActiva->created_at) {
            return \Carbon\Carbon::parse($cajaActiva->created_at);
        }

        if ($cajaActiva->fecha_apertura) {
            return \Carbon\Carbon::parse($cajaActiva->fecha_apertura)->startOfDay();
        }

        return today();
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
        $scope = $request->input('scope');

        $startTime = ($cajaActiva) ? $this->getTurnoStartTime($cajaActiva, $local->id) : null;
        $applyDateFilter = function($query) use ($date, $request, $startTime, $isHoy, $scope) {
            if ($scope === 'all') return $query;
            if ($scope === 'day') return $query->whereDate('created_at', $date);
            if ($scope === 'turno' && $startTime) return $query->where('created_at', '>=', $startTime);
            if ($startTime && $isHoy) return $query->where('created_at', '>=', $startTime);
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
                'origen'        => $i->origen ?? 'mostrador',
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
                'origen'        => $s->origen ?? 'mostrador',
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
                'origen'             => 'mostrador',
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

        // Ingresos del día (cobros de deuda, cobros de turnos inmediatos, servicios a cuenta e ingresos de Tesorería)
        $ingresosQuery = Ingreso::with('cliente')->where('id_local', $local->id);
        $ingresosQuery = $applyDateFilter($ingresosQuery);
        $ingresosData = $ingresosQuery->get();

        $ingresos = $ingresosData->map(function($i) use ($clasificarIngreso, $resolverMetodoPago) {
            $isCuentaCorriente = $i->tipo_pago === 'cuenta_corriente';
            $category = $clasificarIngreso($i);
            $metodo = $resolverMetodoPago($i);
            $isCaja0 = ($i->origen ?? 'mostrador') === 'caja_0';

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
                'category'      => $isCaja0 ? 'caja_0' : $category,
                'amount'        => $isCuentaCorriente ? 0 : (float) $i->monto,
                'totalVenta'    => (float) $i->monto,
                'saldo'         => (float) $i->saldo,
                'description'   => $desc ?? ($isCuentaCorriente ? 'Servicio a cuenta' : 'Ingreso manual'),
                'paymentMethod' => $metodo,
                'origen'        => $i->origen ?? 'mostrador',
                'createdAt'     => $i->created_at?->toISOString(),
                'saleId'        => null,
                'items'         => [],
            ];
        });

        // Egresos del día / turno
        $salidasQuery = Salida::where('id_local', $local->id);
        $salidasQuery = $applyDateFilter($salidasQuery);
        $salidasData = $salidasQuery->get();

        // Para el arqueo de mostrador (cajón diario), SOLO sumamos egresos del mostrador (no Caja 0)
        $salidasMostrador = $salidasData->filter(fn($s) => $s->origen !== 'caja_0');
        $gastosMostrador      = $salidasMostrador->filter(fn($s) => $s->tipo_salida !== 'alivio');
        $egresosEfectivo      = (float) $gastosMostrador->where('tipo_pago', '!=', 'transferencia')->sum('monto');
        $egresosTransferencia = (float) $gastosMostrador->where('tipo_pago', 'transferencia')->sum('monto');

        $aliviosData          = $salidasMostrador->filter(fn($s) => $s->tipo_salida === 'alivio');
        $aliviosEfectivo      = (float) $aliviosData->where('tipo_pago', '!=', 'transferencia')->sum('monto');
        $aliviosTransferencia = (float) $aliviosData->where('tipo_pago', 'transferencia')->sum('monto');
        $totalAlivios         = $aliviosEfectivo + $aliviosTransferencia;

        $salidas = $salidasData->map(function($s) {
            $isComision = $s->tipo_salida === 'comision' || str_contains(strtolower($s->descripcion ?? ''), 'comisi');
            $isCaja0 = $s->origen === 'caja_0';
            return [
                'id'            => 'SAL-' . $s->idsalida,
                'type'          => 'egreso',
                'category'      => $isCaja0 ? 'caja_0' : ($s->tipo_salida === 'alivio' ? 'alivio' : ($isComision ? 'comision' : 'egreso')),
                'amount'        => (float) $s->monto,
                'description'   => $s->descripcion ?? 'Egreso',
                'paymentMethod' => $s->tipo_pago ?? 'efectivo',
                'origen'        => $s->origen ?? 'mostrador',
                'createdAt'     => $s->created_at?->toISOString(),
                'saleId'        => null,
                'items'         => [],
            ];
        });

        // Resumen por forma de pago (sumando ventas + ingresos directos del Mostrador)
        $ingresosMostrador = $ingresosData->filter(fn($i) => ($i->origen ?? 'mostrador') !== 'caja_0');
        $ingresosCaja0     = $ingresosData->filter(fn($i) => ($i->origen ?? 'mostrador') === 'caja_0');
        $ingresosCobrados  = $ingresosMostrador->where('tipo_pago', '!=', 'cuenta_corriente');
        
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
        $serviciosCuenta = (float) $ingresosMostrador->where('tipo_pago', 'cuenta_corriente')->filter(function($i) use ($clasificarIngreso) {
            return $clasificarIngreso($i) === 'servicio';
        })->sum('saldo');

        $resumen = [
            'efectivo'                      => $efectivoVentas + $efectivoIngresos,
            'transferencia'                 => $transferenciaVentas + $transferenciaIngresos,
            'cobros_deuda'                  => $cobrosDeuda,
            'ventas_cuenta'                 => $ventasCuenta + $serviciosCuenta,
            'total_productos'               => $totalProductos,
            'total_servicios'               => $totalServicios,
            'total_otros_ingresos'          => $totalOtrosIngresos,
            'total_ingresos'                => $totalServicios + $cobrosDeuda + $totalOtrosIngresos,
            'egresos'                       => (float) ($egresosEfectivo + $egresosTransferencia),
            'egresos_efectivo'              => $egresosEfectivo,
            'egresos_transferencia'         => $egresosTransferencia,
            'total_alivios'                 => $totalAlivios,
            'alivios_efectivo'              => $aliviosEfectivo,
            'alivios_transferencia'         => $aliviosTransferencia,
            'egresos_caja_0'                => (float) $salidasData->where('origen', 'caja_0')->sum('monto'),
            'ingresos_caja_0'               => (float) $ingresosCaja0->sum('monto'),
            'ingresos_caja_0_efectivo'      => (float) $ingresosCaja0->where('tipo_pago', '!=', 'transferencia')->sum('monto'),
            'ingresos_caja_0_transferencia' => (float) $ingresosCaja0->where('tipo_pago', 'transferencia')->sum('monto'),
            'turno_activo'                  => $cajaActiva ? true : false,
            'turno_inicio'                  => ($startTime instanceof \Carbon\Carbon ? $startTime : ($startTime ? \Carbon\Carbon::parse($startTime) : null))?->toISOString(),
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
            'origen'      => 'nullable|string|in:mostrador,caja_0',
        ]);

        $origen = $request->input('origen', 'mostrador');

        if ($request->type === 'ingreso') {
            $isCuentaCorriente = ($request->tipo_pago === 'cuenta_corriente');
            $tipoPago = $request->input('tipo_pago') ?? $request->input('paymentMethod') ?? 'efectivo';
            $tipoIngreso = $request->input('tipo_ingreso') ?: ($origen === 'caja_0' ? 'aporte_tesoreria' : 'manual');

            $entry = Ingreso::create([
                'idpersona'    => $request->idpersona,
                'monto'        => $request->amount,
                'tipo_pago'    => $tipoPago,
                'tipo_ingreso' => $tipoIngreso,
                'origen'       => $origen,
                'descripcion'  => $request->description,
                'saldo'        => $isCuentaCorriente ? $request->amount : 0,
                'estado'       => 'activo',
                'id_local'     => $local->id,
            ]);
            $id = 'ING-' . $entry->id_ingreso;

            // Si el ingreso entra a Caja 0 (Tesorería), sumar al acumulador del local
            if ($origen === 'caja_0') {
                if ($tipoPago === 'transferencia') {
                    $local->increment('saldo_caja_0_transferencia', (float) $request->amount);
                } else {
                    $local->increment('saldo_caja_0_efectivo', (float) $request->amount);
                }
            }
        } else {
            $tipoPago = $request->input('tipo_pago') ?? $request->input('paymentMethod') ?? 'efectivo';
            $entry = Salida::create([
                'idpersona'   => $request->idpersona,
                'tipo_salida' => $request->input('tipo_salida', 'gasto'),
                'origen'      => $origen,
                'monto'       => $request->amount,
                'tipo_pago'   => $tipoPago,
                'descripcion' => $request->description,
                'saldo'       => $request->amount,
                'estado'      => 'activo',
                'id_local'    => $local->id,
            ]);
            $id = 'SAL-' . $entry->idsalida;

            // Si el egreso sale de Caja 0 (Tesorería), restar del acumulador del local
            if ($origen === 'caja_0') {
                if ($tipoPago === 'transferencia') {
                    $local->decrement('saldo_caja_0_transferencia', (float) $request->amount);
                } else {
                    $local->decrement('saldo_caja_0_efectivo', (float) $request->amount);
                }
            }
        }

        return response()->json([
            'message'  => 'Movimiento registrado',
            'cashflow' => [
                'id'            => $id,
                'type'          => $request->type,
                'category'      => $origen === 'caja_0' ? 'caja_0' : ($request->type === 'ingreso' ? 'ingreso' : 'egreso'),
                'amount'        => (float) $request->amount,
                'description'   => $request->description,
                'paymentMethod' => $entry->tipo_pago ?? 'efectivo',
                'origen'        => $origen,
                'createdAt'     => $entry->created_at->toISOString(),
                'saleId'        => null,
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

            if ($ingreso->origen === 'caja_0') {
                $diferencia = (float)$monto - (float)$ingreso->monto;
                if ($ingreso->tipo_pago === 'transferencia') {
                    $local->increment('saldo_caja_0_transferencia', $diferencia);
                } else {
                    $local->increment('saldo_caja_0_efectivo', $diferencia);
                }
            }

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

            if ($salida->origen === 'caja_0') {
                $diferencia = (float)$monto - (float)$salida->monto;
                if ($salida->tipo_pago === 'transferencia') {
                    $local->decrement('saldo_caja_0_transferencia', $diferencia);
                } else {
                    $local->decrement('saldo_caja_0_efectivo', $diferencia);
                }
            }

            $salida->monto = $monto;
            if ($desc !== null) {
                $salida->descripcion = $desc;
            }
            if ($request->has('paymentMethod') || $request->has('tipo_pago')) {
                $salida->tipo_pago = $request->input('tipo_pago') ?? $request->input('paymentMethod') ?? 'efectivo';
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

            // Si el ingreso fue a Caja 0, restar del saldo al local
            if ($ingreso->origen === 'caja_0') {
                if ($ingreso->tipo_pago === 'transferencia') {
                    $local->decrement('saldo_caja_0_transferencia', (float) $ingreso->monto);
                } else {
                    $local->decrement('saldo_caja_0_efectivo', (float) $ingreso->monto);
                }
            }
            
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
            
            // Si la salida fue de Caja 0, restituir el saldo al local
            if ($salida->origen === 'caja_0') {
                if ($salida->tipo_pago === 'transferencia') {
                    $local->increment('saldo_caja_0_transferencia', (float) $salida->monto);
                } else {
                    $local->increment('saldo_caja_0_efectivo', (float) $salida->monto);
                }
            }

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
                'estado'                     => 'cerrada',
                'caja'                       => null,
                'saldo_caja_0_efectivo'      => (float) ($local->saldo_caja_0_efectivo ?? 0),
                'saldo_caja_0_transferencia' => (float) ($local->saldo_caja_0_transferencia ?? 0),
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

        $startTime = $this->getTurnoStartTime($caja, $local->id);
        $fechaCierre = $request->input('fecha_cierre');
        $endTime = $fechaCierre ? \Carbon\Carbon::parse($fechaCierre)->endOfDay() : null;

        $ventasQuery   = Venta::where('id_local', $local->id)->where('created_at', '>=', $startTime);
        $ingresosQuery = Ingreso::where('id_local', $local->id)
            ->where('created_at', '>=', $startTime)
            ->where('tipo_pago', '!=', 'cuenta_corriente')
            ->where(function($q) {
                $q->whereNull('origen')->orWhere('origen', '!=', 'caja_0');
            });
        $salidasQuery  = Salida::where('id_local', $local->id)
            ->where('created_at', '>=', $startTime)
            ->where(function($q) {
                $q->whereNull('origen')->orWhere('origen', '!=', 'caja_0');
            });

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
        $salidasEfectivo       = (float) $salidas->where('tipo_pago', '!=', 'transferencia')->sum('monto');
        $salidasTransferencia  = (float) $salidas->where('tipo_pago', 'transferencia')->sum('monto');
        $gastosSalidas         = $salidas->where('tipo_salida', '!=', 'alivio');
        $gastosEfectivo        = (float) $gastosSalidas->where('tipo_pago', '!=', 'transferencia')->sum('monto');
        $gastosTransferencia   = (float) $gastosSalidas->where('tipo_pago', 'transferencia')->sum('monto');
        $totalGastos           = $gastosEfectivo + $gastosTransferencia;
        $aliviosEfectivo       = (float) $salidas->where('tipo_salida', 'alivio')->where('tipo_pago', '!=', 'transferencia')->sum('monto');
        $aliviosTransferencia  = (float) $salidas->where('tipo_salida', 'alivio')->where('tipo_pago', 'transferencia')->sum('monto');
        $totalAlivios          = (float) $salidas->where('tipo_salida', 'alivio')->sum('monto');
        $efectivoTotal         = $efectivoVentas + $efectivoIngresos;
        $transferenciaTotal    = $transferenciaVentas + $transferenciaIngresos;

        $totalVentasProductos   = (float) $ventas->sum('pago');
        $totalServiciosIngresos = (float) $ingresos->sum('monto');
        $totalVentasCuenta      = (float) $ventas->where('forma_de_pago', 'cuenta_corriente')->sum('saldo');

        $now = now();
        $createdAt = ($startTime instanceof \Carbon\Carbon) ? $startTime : \Carbon\Carbon::parse($startTime);
        $diffHours = (int) $createdAt->diffInHours($now);
        $diffDays  = (int) $createdAt->diffInDays($now);
        
        $duracionTexto = '';
        if ($diffDays > 0) {
            $remainingHours = $diffHours % 24;
            $duracionTexto = "{$diffDays} " . ($diffDays === 1 ? 'día' : 'días') . ($remainingHours > 0 ? " y {$remainingHours} hs" : "");
        } elseif ($diffHours > 0) {
            $duracionTexto = "{$diffHours} " . ($diffHours === 1 ? 'hora' : 'horas');
        } else {
            $diffMins = (int) $createdAt->diffInMinutes($now);
            $duracionTexto = "{$diffMins} min";
        }

        return response()->json([
            'estado'                     => 'abierta',
            'saldo_caja_0_efectivo'      => (float) ($local->saldo_caja_0_efectivo ?? 0),
            'saldo_caja_0_transferencia' => (float) ($local->saldo_caja_0_transferencia ?? 0),
            'caja'   => [
                'id'                           => $caja->id,
                'fecha_apertura'               => $caja->fecha_apertura,
                'created_at'                   => ($startTime instanceof \Carbon\Carbon ? $startTime : \Carbon\Carbon::parse($startTime))->toISOString(),
                'turno_inicio'                 => ($startTime instanceof \Carbon\Carbon ? $startTime : \Carbon\Carbon::parse($startTime))->toISOString(),
                'dias_abierta'                 => $diffDays,
                'horas_abierta'                => $diffHours,
                'duracion_texto'               => $duracionTexto,
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
                'egresos'                      => $totalGastos,
                'egresos_efectivo'             => $gastosEfectivo,
                'egresos_transferencia'        => $gastosTransferencia,
                'efectivo_en_caja'             => round((float)$caja->monto_apertura + $efectivoTotal - $salidasEfectivo, 2),
                'transferencia_en_caja'        => round((float)$caja->monto_apertura_transferencia + $transferenciaTotal - $salidasTransferencia, 2),
                'total_alivios'                => $totalAlivios,
                'alivios_efectivo'             => $aliviosEfectivo,
                'alivios_transferencia'        => $aliviosTransferencia,
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
        $startTime = $this->getTurnoStartTime($caja, $local->id);
        $endTime   = \Carbon\Carbon::parse($fechaCierre)->endOfDay();

        $ventas   = Venta::where('id_local', $local->id)->where('created_at', '>=', $startTime)->where('created_at', '<=', $endTime)->get();
        $ingresos = Ingreso::where('id_local', $local->id)
            ->where('created_at', '>=', $startTime)
            ->where('created_at', '<=', $endTime)
            ->where(function($q) {
                $q->whereNull('origen')->orWhere('origen', '!=', 'caja_0');
            })
            ->get();
        $salidas  = Salida::where('id_local', $local->id)
            ->where('created_at', '>=', $startTime)
            ->where('created_at', '<=', $endTime)
            ->where(function($q) {
                $q->whereNull('origen')->orWhere('origen', '!=', 'caja_0');
            })
            ->get();

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
        $salidasEfectivo       = (float) $salidas->where('tipo_pago', '!=', 'transferencia')->sum('monto');
        $salidasTransferencia  = (float) $salidas->where('tipo_pago', 'transferencia')->sum('monto');
        $totalSalidas          = (float) $salidas->where('tipo_salida', '!=', 'alivio')->sum('monto');

        // Sistema espera esto en físico y en banco para este turno
        $efectivoSistema      = round((float)$caja->monto_apertura + $ventasEfectivo + ($totalIngresos - $ingresosTransferencia) - $salidasEfectivo, 2);
        $transferenciaSistema = round((float)$caja->monto_apertura_transferencia + $ventasTransferencia + $ingresosTransferencia - $salidasTransferencia, 2);

        // Montos reales ingresados manualmente por el operador
        $montoEfectivoReal      = (float) $request->monto_efectivo_real;
        $montoTransferenciaReal = (float) ($request->monto_transferencia_real ?? $transferenciaSistema);
        $montoCuentaReal        = (float) ($request->monto_cuenta_real ?? $ventasCuenta);
        $montoTotalReal         = round($montoEfectivoReal + $montoTransferenciaReal + $montoCuentaReal, 2);

        // Fondo que queda en el cajón para el próximo turno y retiro a Caja 0
        $fondoSiguienteTurno = $request->has('fondo_siguiente_turno') 
            ? (float) $request->fondo_siguiente_turno 
            : $montoEfectivoReal;

        $retiroCaja0Efectivo = $request->has('retiro_caja_0_efectivo')
            ? (float) $request->retiro_caja_0_efectivo
            : max(0, round($montoEfectivoReal - $fondoSiguienteTurno, 2));

        $fondoSiguienteTurnoTransferencia = $request->has('fondo_siguiente_turno_transferencia')
            ? (float) $request->fondo_siguiente_turno_transferencia
            : $montoTransferenciaReal;

        $retiroCaja0Transferencia = $request->has('retiro_caja_0_transferencia')
            ? (float) $request->retiro_caja_0_transferencia
            : max(0, round($montoTransferenciaReal - $fondoSiguienteTurnoTransferencia, 2));

        $fechaCierre = $request->input('fecha_cierre', today()->toDateString());
        $caja->update([
            'fecha_cierre'                        => $fechaCierre,
            'monto_total_ventas'                  => $totalVentas,
            'ventas_efectivo'                     => $ventasEfectivo,
            'ventas_transferencia'                => $ventasTransferencia,
            'ventas_cuenta'                       => $ventasCuenta,
            'ingresos'                            => $totalIngresos,
            'salidas'                             => $totalSalidas,
            'monto_cierre'                        => $efectivoSistema,
            'monto_cierre_real'                   => $montoEfectivoReal,
            'monto_cierre_transferencia_real'     => $montoTransferenciaReal,
            'monto_cierre_cuenta_real'            => $montoCuentaReal,
            'retiro_caja_0_efectivo'              => $retiroCaja0Efectivo,
            'fondo_siguiente_turno'               => $fondoSiguienteTurno,
            'retiro_caja_0_transferencia'         => $retiroCaja0Transferencia,
            'fondo_siguiente_turno_transferencia' => $fondoSiguienteTurnoTransferencia,
            'estado'                              => 'cerrada',
        ]);

        // Impactar retiros en el acumulador de Caja 0 del local
        if ($retiroCaja0Efectivo > 0) {
            $local->increment('saldo_caja_0_efectivo', $retiroCaja0Efectivo);
        }
        if ($retiroCaja0Transferencia > 0) {
            $local->increment('saldo_caja_0_transferencia', $retiroCaja0Transferencia);
        }

        return response()->json([
            'message'                             => 'Caja cerrada correctamente',
            'diferencia_efectivo'                 => round($montoEfectivoReal - $efectivoSistema, 2),
            'efectivo_sistema'                    => $efectivoSistema,
            'monto_efectivo_real'                 => $montoEfectivoReal,
            'monto_transferencia_real'            => $montoTransferenciaReal,
            'monto_cuenta_real'                   => $montoCuentaReal,
            'total_real'                          => $montoTotalReal,
            'retiro_caja_0_efectivo'              => $retiroCaja0Efectivo,
            'fondo_siguiente_turno'               => $fondoSiguienteTurno,
            'retiro_caja_0_transferencia'         => $retiroCaja0Transferencia,
            'fondo_siguiente_turno_transferencia' => $fondoSiguienteTurnoTransferencia,
            'saldo_caja_0_efectivo'               => (float) $local->fresh()->saldo_caja_0_efectivo,
            'saldo_caja_0_transferencia'          => (float) $local->fresh()->saldo_caja_0_transferencia,
            'caja'                                => $caja->fresh(),
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

    /**
     * POST /api/caja/caja-0/ajuste
     * Permite cargar saldo inicial o ajustar el balance de Caja 0 (Efectivo y Transferencia)
     */
    public function alivio(Request $request)
    {
        $local = $this->getLocal();
        if (!$local) return response()->json(['message' => 'No tenés un local asignado.'], 403);

        $montoEfectivo      = (float) $request->input('monto_efectivo', 0);
        $montoTransferencia = (float) $request->input('monto_transferencia', 0);

        // Fallback legacy (monto + tipo_pago)
        if ($montoEfectivo <= 0 && $montoTransferencia <= 0 && $request->has('monto')) {
            $m = (float) $request->input('monto', 0);
            if ($request->input('tipo_pago') === 'transferencia') {
                $montoTransferencia = $m;
            } else {
                $montoEfectivo = $m;
            }
        }

        if ($montoEfectivo <= 0 && $montoTransferencia <= 0) {
            return response()->json(['message' => 'Ingresá un monto válido para transferir a Tesorería.'], 422);
        }

        $desc = $request->input('descripcion') ?: 'Alivio de caja a Tesorería';

        DB::beginTransaction();
        try {
            $salidas = [];

            if ($montoEfectivo > 0) {
                $salidaEf = Salida::create([
                    'tipo_salida' => 'alivio',
                    'origen'      => 'mostrador',
                    'monto'       => $montoEfectivo,
                    'tipo_pago'   => 'efectivo',
                    'descripcion' => $desc . ($montoTransferencia > 0 ? ' (Efectivo)' : ''),
                    'saldo'       => $montoEfectivo,
                    'estado'      => 'activo',
                    'id_local'    => $local->id,
                ]);
                $local->increment('saldo_caja_0_efectivo', $montoEfectivo);
                $salidas[] = $salidaEf;
            }

            if ($montoTransferencia > 0) {
                $salidaTr = Salida::create([
                    'tipo_salida' => 'alivio',
                    'origen'      => 'mostrador',
                    'monto'       => $montoTransferencia,
                    'tipo_pago'   => 'transferencia',
                    'descripcion' => $desc . ($montoEfectivo > 0 ? ' (Banco/MP)' : ''),
                    'saldo'       => $montoTransferencia,
                    'estado'      => 'activo',
                    'id_local'    => $local->id,
                ]);
                $local->increment('saldo_caja_0_transferencia', $montoTransferencia);
                $salidas[] = $salidaTr;
            }

            DB::commit();

            return response()->json([
                'success'                    => true,
                'message'                    => 'Alivio a Tesorería registrado correctamente',
                'saldo_caja_0_efectivo'      => (float) $local->fresh()->saldo_caja_0_efectivo,
                'saldo_caja_0_transferencia' => (float) $local->fresh()->saldo_caja_0_transferencia,
                'salidas'                    => $salidas,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al registrar alivio: ' . $e->getMessage()], 500);
        }
    }

    public function ajusteCaja0(Request $request)
    {
        $local = $this->getLocal();
        if (!$local) {
            return response()->json(['message' => 'No tenés un local asignado.'], 403);
        }

        $request->validate([
            'saldo_efectivo'      => 'required|numeric|min:0',
            'saldo_transferencia' => 'nullable|numeric|min:0',
            'motivo'              => 'nullable|string|max:255',
        ]);

        $saldoEf = (float) $request->saldo_efectivo;
        $saldoTr = (float) ($request->saldo_transferencia ?? 0);
        $motivo  = trim($request->motivo ?: 'Ajuste de Tesorería');

        $actualEf = (float) ($local->saldo_caja_0_efectivo ?? 0);
        $actualTr = (float) ($local->saldo_caja_0_transferencia ?? 0);

        $diffEf = round($saldoEf - $actualEf, 2);
        $diffTr = round($saldoTr - $actualTr, 2);

        DB::beginTransaction();
        try {
            // Ajuste Efectivo
            if ($diffEf > 0) {
                Ingreso::create([
                    'idpersona'    => null,
                    'monto'        => $diffEf,
                    'tipo_pago'    => 'efectivo',
                    'tipo_ingreso' => 'ajuste_caja_0',
                    'origen'       => 'caja_0',
                    'descripcion'  => "Ajuste positivo Tesorería: {$motivo}",
                    'saldo'        => 0,
                    'estado'       => 'activo',
                    'id_local'     => $local->id,
                ]);
            } elseif ($diffEf < 0) {
                Salida::create([
                    'idpersona'   => null,
                    'tipo_salida' => 'ajuste_caja_0',
                    'origen'      => 'caja_0',
                    'monto'       => abs($diffEf),
                    'tipo_pago'   => 'efectivo',
                    'descripcion' => "Ajuste negativo Tesorería: {$motivo}",
                    'saldo'       => abs($diffEf),
                    'estado'      => 'activo',
                    'id_local'    => $local->id,
                ]);
            }

            // Ajuste Transferencia
            if ($diffTr > 0) {
                Ingreso::create([
                    'idpersona'    => null,
                    'monto'        => $diffTr,
                    'tipo_pago'    => 'transferencia',
                    'tipo_ingreso' => 'ajuste_caja_0',
                    'origen'       => 'caja_0',
                    'descripcion'  => "Ajuste positivo Tesorería (Banco): {$motivo}",
                    'saldo'        => 0,
                    'estado'       => 'activo',
                    'id_local'     => $local->id,
                ]);
            } elseif ($diffTr < 0) {
                Salida::create([
                    'idpersona'   => null,
                    'tipo_salida' => 'ajuste_caja_0',
                    'origen'      => 'caja_0',
                    'monto'       => abs($diffTr),
                    'tipo_pago'   => 'transferencia',
                    'descripcion' => "Ajuste negativo Tesorería (Banco): {$motivo}",
                    'saldo'       => abs($diffTr),
                    'estado'      => 'activo',
                    'id_local'    => $local->id,
                ]);
            }

            $local->saldo_caja_0_efectivo = $saldoEf;
            $local->saldo_caja_0_transferencia = $saldoTr;
            $local->save();

            DB::commit();

            return response()->json([
                'success'                    => true,
                'message'                    => 'Saldo de Caja 0 actualizado y movimiento registrado correctamente',
                'saldo_caja_0_efectivo'      => $saldoEf,
                'saldo_caja_0_transferencia' => $saldoTr,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al ajustar Caja 0: ' . $e->getMessage()], 500);
        }
    }
}
