<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Servicio;
use App\Models\Pedido;
use App\Models\DetallePedido;
use App\Models\HorarioDisponibilidad;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TurnoController extends Controller
{
    // 1. Pública: Consultar cuánta gente hay y hora estimada
    public function disponibilidad(Request $request, $servicioId)
    {
        $fecha = $request->query('fecha', date('Y-m-d'));
        $recursoId = $request->query('recurso_id');
        
        $servicio = Servicio::with('local')->findOrFail($servicioId);

        $cerrado = false;
        // Si de_turno existe en la base de datos y es estrictamente 0 o "0" (apagado)
        if (isset($servicio->local) && $servicio->local->de_turno !== null && (int)$servicio->local->de_turno === 0) {
            $cerrado = true;
        }

        if ($servicio->tipo_reserva === 'cola_virtual') {
            // Contar cuántos están esperando hoy
            $query = Pedido::where('tipo_pedido', 'servicio')
                ->whereDate('fecha_servicio', $fecha)
                ->whereIn('estado_atencion', ['en_espera', 'siendo_atendido'])
                ->whereHas('detalles', function ($q) use ($servicioId, $recursoId) {
                    $q->where('idservicio', $servicioId);
                    if ($recursoId) {
                        $q->where('recurso_id', $recursoId);
                    }
                });

            $personasEnCola = $query->count();
            
            // Lógica simple de estimación (Duración servicio + buffer)
            $minutosPorTurno = ($servicio->duracion ?? 30) + ($servicio->buffer_tiempo ?? 0);
            $minutosTotalesEspera = $personasEnCola * $minutosPorTurno;
            
            $horaEstimada = Carbon::now()->addMinutes($minutosTotalesEspera)->format('H:i');

            return response()->json([
                'tipo' => 'cola_virtual',
                'personas_en_cola' => $personasEnCola,
                'hora_estimada_proxima' => $horaEstimada,
                'minutos_espera' => $minutosTotalesEspera,
                'cerrado' => $cerrado
            ]);
        }

        // Si es turno fijo tradicional, devolver array de slots libres
        $slots = [];
        $duracion = ($servicio->duracion ?? 30) + ($servicio->buffer_tiempo ?? 0);
        if ($duracion <= 0) $duracion = 30; // Fallback mínimo

        if ($servicio->local && !$cerrado) {
            $diaSemanaIngles = Carbon::parse($fecha, 'America/Argentina/Buenos_Aires')->englishDayOfWeek; // Ej: Monday
            $mapaDias = [
                'Monday' => 'lunes', 'Tuesday' => 'martes', 'Wednesday' => 'miercoles',
                'Thursday' => 'jueves', 'Friday' => 'viernes', 'Saturday' => 'sabado', 'Sunday' => 'domingo'
            ];
            $diaClave = $mapaDias[$diaSemanaIngles] ?? 'lunes';

            $horariosLocal = json_decode($servicio->local->horario, true);
            $siempreAbierto = $servicio->local->siempre_abierto ?? false;

            $turnosDelDia = [];
            if ($siempreAbierto) {
                $turnosDelDia = [['apertura' => '00:00', 'cierre' => '23:59']];
            } elseif (is_array($horariosLocal) && isset($horariosLocal[$diaClave])) {
                $configDia = $horariosLocal[$diaClave];
                
                // Formato Nuevo: { abierto: true, turnos: [{apertura: '09', cierre: '18'}] }
                if (is_array($configDia) && isset($configDia['abierto'])) {
                    if ($configDia['abierto'] && isset($configDia['turnos'])) {
                        $turnosDelDia = $configDia['turnos'];
                    }
                } 
                // Formato Viejo: ["09:00-13:00", "17:00-21:00"]
                elseif (is_array($configDia)) {
                    foreach ($configDia as $rango) {
                        $partes = explode('-', $rango);
                        if (count($partes) === 2) {
                            $turnosDelDia[] = ['apertura' => trim($partes[0]), 'cierre' => trim($partes[1])];
                        }
                    }
                }
                // Formato String único: "09:00-18:00"
                elseif (is_string($configDia) && strtolower(trim($configDia)) !== 'cerrado') {
                    $partes = explode('-', $configDia);
                    if (count($partes) === 2) {
                        $turnosDelDia[] = ['apertura' => trim($partes[0]), 'cierre' => trim($partes[1])];
                    }
                }
            }

            // Reservas existentes con su rango de duración
            $reservas = Pedido::with('detalles.servicio')
                ->where('tipo_pedido', 'servicio')
                ->whereDate('fecha_servicio', $fecha)
                ->whereIn('estado_atencion', ['en_espera', 'siendo_atendido', 'atendido'])
                ->whereHas('detalles', function ($q) use ($servicioId, $recursoId) {
                    $q->where('idservicio', $servicioId);
                    if ($recursoId) {
                        $q->where('recurso_id', $recursoId);
                    }
                })
                ->whereNotNull('hora_inicio')
                ->get();

            $rangosOcupados = [];
            foreach ($reservas as $reserva) {
                if (!$reserva->hora_inicio) continue;
                $ini = Carbon::parse($fecha . ' ' . $reserva->hora_inicio, 'America/Argentina/Buenos_Aires');
                $det = $reserva->detalles->first();
                $durReserva = $duracion;
                if ($det && $det->servicio) {
                    $durReserva = ($det->servicio->duracion ?? 30) + ($det->servicio->buffer_tiempo ?? 0);
                    if ($durReserva <= 0) $durReserva = 30;
                }
                $fin = $ini->copy()->addMinutes($durReserva);
                $rangosOcupados[] = [
                    'inicio' => $ini,
                    'fin'    => $fin,
                ];
            }

            // Bloqueos de horarios inhabilitados por el administrador
            $bloqueos = \App\Models\BloqueoHorario::where('id_local', $servicio->id_local)
                ->whereDate('fecha', $fecha)
                ->where(function ($q) use ($servicioId) {
                    $q->whereNull('idservicio')->orWhere('idservicio', $servicioId);
                })
                ->get();

            $rangosBloqueados = [];
            foreach ($bloqueos as $b) {
                $bIni = Carbon::parse($fecha . ' ' . $b->hora_inicio, 'America/Argentina/Buenos_Aires');
                $bFin = Carbon::parse($fecha . ' ' . $b->hora_fin, 'America/Argentina/Buenos_Aires');
                $rangosBloqueados[] = [
                    'id'     => $b->id,
                    'inicio' => $bIni,
                    'fin'    => $bFin,
                    'motivo' => $b->motivo ?? 'Inhabilitado',
                ];
            }

            $ahora = Carbon::now('America/Argentina/Buenos_Aires');
            $esHoy = Carbon::parse($fecha, 'America/Argentina/Buenos_Aires')->isToday();

            foreach ($turnosDelDia as $turno) {
                if (empty($turno['apertura']) || empty($turno['cierre'])) continue;

                $inicioTurno = Carbon::parse($fecha . ' ' . $turno['apertura'], 'America/Argentina/Buenos_Aires');
                $finTurno = Carbon::parse($fecha . ' ' . $turno['cierre'], 'America/Argentina/Buenos_Aires');
                $currentSlot = clone $inicioTurno;

                while ($currentSlot->copy()->addMinutes($duracion)->lte($finTurno)) {
                    $slotInicio = $currentSlot->copy();
                    $slotFin = $currentSlot->copy()->addMinutes($duracion);
                    $horaStr = $slotInicio->format('H:i');
                    $esFuturo = !$esHoy || $slotInicio->gt($ahora);
                    
                    // Comprobar si solapa con turnos tomados
                    $solapado = false;
                    foreach ($rangosOcupados as $ocupado) {
                        if ($slotInicio->lt($ocupado['fin']) && $slotFin->gt($ocupado['inicio'])) {
                            $solapado = true;
                            break;
                        }
                    }

                    // Comprobar si solapa con algún horario inhabilitado/bloqueado
                    $bloqueoEncontrado = null;
                    foreach ($rangosBloqueados as $bloq) {
                        if ($slotInicio->lt($bloq['fin']) && $slotFin->gt($bloq['inicio'])) {
                            $bloqueoEncontrado = $bloq;
                            break;
                        }
                    }

                    if ($esFuturo) {
                        $slots[] = [
                            'hora'       => $horaStr,
                            'disponible' => !$solapado && is_null($bloqueoEncontrado),
                            'bloqueado'  => !is_null($bloqueoEncontrado),
                            'motivo'     => $bloqueoEncontrado ? $bloqueoEncontrado['motivo'] : null,
                            'bloqueo_id' => $bloqueoEncontrado ? $bloqueoEncontrado['id'] : null,
                        ];
                    }
                    $currentSlot->addMinutes($duracion);
                }
            }
        }

        $debug = [
            'fecha' => $fecha,
            'diaClave' => $diaClave ?? null,
            'horario_raw' => $servicio->local->horario ?? null,
            'turnosDelDia' => $turnosDelDia ?? [],
            'esHoy' => $esHoy ?? false,
            'ahora' => isset($ahora) ? $ahora->format('H:i') : null,
            'duracion' => $duracion ?? null,
            'rangosOcupados' => array_map(function($r) {
                return $r['inicio']->format('H:i') . ' - ' . $r['fin']->format('H:i');
            }, $rangosOcupados),
        ];

        \Illuminate\Support\Facades\Log::info('Turno fijo debug:', $debug);

        return response()->json([
            'tipo'     => 'turno_fijo',
            'slots'    => $slots,
            'bloqueos' => $bloqueos ?? [],
            'cerrado'  => $cerrado,
            'debug'    => $debug
        ]);
    }

    // 2. Pública: Cliente reserva turno
    public function store(Request $request)
    {
        $request->validate([
            'id_local' => 'required|integer',
            'idservicio' => 'required|integer',
            'nombre_cliente' => 'required|string',
            'telefono' => 'required|string',
            'fecha_servicio' => 'required|date',
            'slot_hora' => 'nullable|string', // Cambiado a slot_hora para que coincida con el frontend
        ]);

        $servicio = Servicio::with('local')->findOrFail($request->idservicio);

        // Verificar que el local esté aceptando turnos
        if (isset($servicio->local) && $servicio->local->de_turno !== null && (int)$servicio->local->de_turno === 0) {
            return response()->json([
                'success' => false,
                'error'   => 'El local no está aceptando turnos en este momento.'
            ], 422);
        }

        DB::beginTransaction();
        try {
            $esFijo = $servicio->tipo_reserva === 'turno_fijo';
            $miPosicion = 0;
            $horaEstimada = null;
            $minutosPorTurno = ($servicio->duracion ?? 30) + ($servicio->buffer_tiempo ?? 0);
            if ($minutosPorTurno <= 0) $minutosPorTurno = 30;

            if ($esFijo && $request->slot_hora) {
                $nuevoInicio = Carbon::parse($request->fecha_servicio . ' ' . $request->slot_hora, 'America/Argentina/Buenos_Aires');
                $nuevoFin = $nuevoInicio->copy()->addMinutes($minutosPorTurno);

                $hayChoque = Pedido::where('tipo_pedido', 'servicio')
                    ->whereDate('fecha_servicio', $request->fecha_servicio)
                    ->whereIn('estado_atencion', ['en_espera', 'siendo_atendido', 'atendido'])
                    ->whereHas('detalles', function ($q) use ($servicio, $request) {
                        $q->where('idservicio', $servicio->idservicio);
                        if ($request->recurso_id) {
                            $q->where('recurso_id', $request->recurso_id);
                        }
                    })
                    ->whereNotNull('hora_inicio')
                    ->get()
                    ->some(function ($p) use ($nuevoInicio, $nuevoFin, $minutosPorTurno) {
                        $ini = Carbon::parse($p->fecha_servicio . ' ' . $p->hora_inicio, 'America/Argentina/Buenos_Aires');
                        $fin = $ini->copy()->addMinutes($minutosPorTurno);
                        return $nuevoInicio->lt($fin) && $nuevoFin->gt($ini);
                    });

                if ($hayChoque) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'error'   => 'El horario seleccionado ya no está disponible (se solapa con otro turno).'
                    ], 422);
                }

                $hayBloqueo = \App\Models\BloqueoHorario::where('id_local', $request->id_local)
                    ->whereDate('fecha', $request->fecha_servicio)
                    ->where(function ($q) use ($servicio) {
                        $q->whereNull('idservicio')->orWhere('idservicio', $servicio->idservicio);
                    })
                    ->get()
                    ->some(function ($b) use ($nuevoInicio, $nuevoFin) {
                        $bIni = Carbon::parse($b->fecha . ' ' . $b->hora_inicio, 'America/Argentina/Buenos_Aires');
                        $bFin = Carbon::parse($b->fecha . ' ' . $b->hora_fin, 'America/Argentina/Buenos_Aires');
                        return $nuevoInicio->lt($bFin) && $nuevoFin->gt($bIni);
                    });

                if ($hayBloqueo) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'error'   => 'El horario seleccionado está inhabilitado por el administrador.'
                    ], 422);
                }
            }

            if (!$esFijo) {
                // 1. Calcular posición en cola (MAX + 1)
                $ultimaPosicion = Pedido::where('tipo_pedido', 'servicio')
                    ->whereDate('fecha_servicio', $request->fecha_servicio)
                    ->where('id_local', $request->id_local)
                    ->whereHas('detalles', function($q) use ($request) {
                        $q->where('idservicio', $request->idservicio);
                        if ($request->recurso_id) {
                            $q->where('recurso_id', $request->recurso_id);
                        }
                    })
                    ->max('posicion_cola') ?? 0;
                
                $miPosicion = $ultimaPosicion + 1;

                // 2. Calcular hora estimada estática inicial
                $minutosPorTurno = ($servicio->duracion ?? 30) + ($servicio->buffer_tiempo ?? 0);
                $horaEstimada = Carbon::now()->addMinutes(($miPosicion - 1) * $minutosPorTurno)->format('H:i:s');
            }

            // 3. Crear el Pedido (Turno)
            $pedido = Pedido::create([
                'id_local' => $request->id_local,
                'nombre_cliente' => $request->nombre_cliente,
                'telefono' => $request->telefono,
                'notas_entrega' => $request->notas_entrega,
                'tipo_pedido' => 'servicio',
                'estado' => 'pendiente',
                'estado_reserva' => 'confirmada',
                'fecha_servicio' => $request->fecha_servicio,
                'hora_inicio' => $request->slot_hora, // Usamos slot_hora
                'posicion_cola' => $miPosicion,
                'hora_estimada' => $esFijo && $request->slot_hora ? $request->slot_hora . ':00' : $horaEstimada,
                'estado_atencion' => 'en_espera',
                'token_publico' => Str::random(32),
                'subtotal' => $servicio->precio,
                'total' => $servicio->precio,
            ]);

            // 4. Crear detalle
            DetallePedido::create([
                'pedido_id' => $pedido->id,
                'idservicio' => $servicio->idservicio,
                'recurso_id' => $request->recurso_id ?? null,
                'cantidad' => 1,
                'precio_unitario' => $servicio->precio,
                'subtotal' => $servicio->precio,
            ]);

            // Registrar automáticamente el cliente en "Mis Clientes" si no existe
            $nombreCliente = trim($request->nombre_cliente ?? '');
            $telefonoCliente = trim($request->telefono ?? '');

            if (!empty($nombreCliente)) {
                $clienteExistente = null;
                if (!empty($telefonoCliente) && $telefonoCliente !== '0') {
                    $clienteExistente = \App\Models\Persona::where('id_local', $request->id_local)
                        ->where('tipo_persona', 'cliente')
                        ->where('telefono', $telefonoCliente)
                        ->first();
                }
                if (!$clienteExistente) {
                    $clienteExistente = \App\Models\Persona::where('id_local', $request->id_local)
                        ->where('tipo_persona', 'cliente')
                        ->where('nombre', $nombreCliente)
                        ->first();
                }

                if (!$clienteExistente) {
                    \App\Models\Persona::create([
                        'tipo_persona' => 'cliente',
                        'nombre'       => $nombreCliente,
                        'telefono'     => !empty($telefonoCliente) && $telefonoCliente !== '0' ? $telefonoCliente : '',
                        'mail'         => '',
                        'estado'       => 'Activo',
                        'id_local'     => $request->id_local,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'mensaje' => 'Turno confirmado',
                'pedido_id' => $pedido->id,
                'token_publico' => $pedido->token_publico,
                'posicion_cola' => $miPosicion,
                'hora_estimada' => $horaEstimada
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // 2b. Admin: Crear turno manual sin validar de_turno
    public function storeAdmin(Request $request)
    {
        $request->validate([
            'idservicio'     => 'required|integer',
            'nombre_cliente' => 'required|string',
            'telefono'       => 'nullable|string',
            'fecha_servicio' => 'required|date',
            'slot_hora'      => 'nullable|string',
        ]);

        $user    = $request->user();
        $local   = \App\Models\Local::where('id_user', $user->id)->firstOrFail();
        $servicio = Servicio::findOrFail($request->idservicio);

        DB::beginTransaction();
        try {
            $esFijo      = $servicio->tipo_reserva === 'turno_fijo';
            $miPosicion  = 0;
            $horaEstimada = null;

            if ($request->slot_hora) {
                $hSlot = $request->slot_hora;
                $slotCheck = Carbon::parse($request->fecha_servicio . ' ' . $hSlot, 'America/Argentina/Buenos_Aires');
                $hayBloqueoAdmin = \App\Models\BloqueoHorario::where('id_local', $local->id)
                    ->whereDate('fecha', $request->fecha_servicio)
                    ->where(function ($q) use ($servicio) {
                        $q->whereNull('idservicio')->orWhere('idservicio', $servicio->idservicio);
                    })
                    ->get()
                    ->some(function ($b) use ($slotCheck) {
                        $bIni = Carbon::parse($b->fecha . ' ' . $b->hora_inicio, 'America/Argentina/Buenos_Aires');
                        $bFin = Carbon::parse($b->fecha . ' ' . $b->hora_fin, 'America/Argentina/Buenos_Aires');
                        return $slotCheck->gte($bIni) && $slotCheck->lt($bFin);
                    });

                if ($hayBloqueoAdmin) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'error'   => 'El horario seleccionado está inhabilitado por un bloqueo de agenda.'
                    ], 422);
                }
            }

            if (!$esFijo) {
                $ultimaPosicion = Pedido::where('tipo_pedido', 'servicio')
                    ->whereDate('fecha_servicio', $request->fecha_servicio)
                    ->where('id_local', $local->id)
                    ->whereHas('detalles', fn($q) => $q->where('idservicio', $request->idservicio))
                    ->max('posicion_cola') ?? 0;

                $miPosicion = $ultimaPosicion + 1;
                $minutosPorTurno = ($servicio->duracion ?? 30) + ($servicio->buffer_tiempo ?? 0);
                $horaEstimada = Carbon::now()->addMinutes(($miPosicion - 1) * $minutosPorTurno)->format('H:i:s');
            }

            $pedido = Pedido::create([
                'id_local'       => $local->id,
                'nombre_cliente' => $request->nombre_cliente,
                'telefono'       => $request->telefono ?? '0',
                'tipo_pedido'    => 'servicio',
                'estado'         => 'pendiente',
                'estado_reserva' => 'confirmada',
                'fecha_servicio' => $request->fecha_servicio,
                'hora_inicio'    => $request->slot_hora,
                'posicion_cola'  => $miPosicion,
                'hora_estimada'  => $esFijo && $request->slot_hora ? $request->slot_hora . ':00' : $horaEstimada,
                'estado_atencion'=> 'en_espera',
                'token_publico'  => Str::random(32),
                'subtotal'       => $servicio->precio,
                'total'          => $servicio->precio,
            ]);

            // Obtener empleado asignado al servicio si existe
            $empleadoId = null;
            if (method_exists($servicio, 'empleados')) {
                $empleadoId = $servicio->empleados()->first()?->idpersona;
            }

            DetallePedido::create([
                'pedido_id'       => $pedido->id,
                'idservicio'      => $servicio->idservicio,
                'id_empleado'     => $empleadoId,
                'cantidad'        => 1,
                'precio_unitario' => $servicio->precio,
                'subtotal'        => $servicio->precio,
            ]);

            // Registrar automáticamente el cliente en "Mis Clientes" si no existe
            $nombreCliente = trim($request->nombre_cliente ?? '');
            $telefonoCliente = trim($request->telefono ?? '');

            if (!empty($nombreCliente)) {
                $clienteExistente = null;
                if (!empty($telefonoCliente) && $telefonoCliente !== '0') {
                    $clienteExistente = \App\Models\Persona::where('id_local', $local->id)
                        ->where('tipo_persona', 'cliente')
                        ->where('telefono', $telefonoCliente)
                        ->first();
                }
                if (!$clienteExistente) {
                    $clienteExistente = \App\Models\Persona::where('id_local', $local->id)
                        ->where('tipo_persona', 'cliente')
                        ->where('nombre', $nombreCliente)
                        ->first();
                }

                if (!$clienteExistente) {
                    \App\Models\Persona::create([
                        'tipo_persona' => 'cliente',
                        'nombre'       => $nombreCliente,
                        'telefono'     => !empty($telefonoCliente) && $telefonoCliente !== '0' ? $telefonoCliente : '',
                        'mail'         => '',
                        'estado'       => 'Activo',
                        'id_local'     => $local->id,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success'   => true,
                'mensaje'   => 'Turno creado por admin',
                'pedido_id' => $pedido->id,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error("Error en storeAdmin: " . $e->getMessage() . " - Line: " . $e->getLine());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // 3. Pública: Trackear estado por token
    public function status($token)
    {
        $pedido = Pedido::with('detalles.servicio', 'detalles.recurso', 'local')
            ->where('token_publico', $token)
            ->firstOrFail();

        // Contar cuántos turnos "en_espera" o "siendo_atendidos" hay con posición MENOR a la mía
        $personasAdelante = 0;
        if ($pedido->posicion_cola !== null) {
            $personasAdelante = Pedido::where('tipo_pedido', 'servicio')
                ->where('id_local', $pedido->id_local)
                ->whereDate('fecha_servicio', $pedido->fecha_servicio)
                ->whereIn('estado_atencion', ['en_espera', 'siendo_atendido'])
                ->where('posicion_cola', '<', $pedido->posicion_cola)
                ->count();
        }

        return response()->json([
            'turno' => $pedido,
            'personas_adelante' => $personasAdelante,
            'servicio' => $pedido->detalles->first()->servicio->nombre ?? 'Servicio',
            'recurso' => $pedido->detalles->first()->recurso->nombre ?? null,
        ]);
    }

    // 4. Admin: Llama a un turno específico (pasa al sillón)
    public function llamar($id)
    {
        $turnoActual = Pedido::findOrFail($id);
        
        DB::beginTransaction();
        try {
            $turnoActual->update(['estado_atencion' => 'siendo_atendido']);
            
            // Recalcular horas estimadas de los demás basándose en la hora REAL de ahora
            $detalle = $turnoActual->detalles->first();
            $minutosPorTurno = ($detalle->servicio->duracion ?? 30) + ($detalle->servicio->buffer_tiempo ?? 0);
            
            $losDemas = Pedido::where('tipo_pedido', 'servicio')
                ->where('id_local', $turnoActual->id_local)
                ->whereDate('fecha_servicio', $turnoActual->fecha_servicio)
                ->where('estado_atencion', 'en_espera')
                ->where('posicion_cola', '>', $turnoActual->posicion_cola)
                ->orderBy('posicion_cola', 'asc')
                ->get();
            
            $horaBase = Carbon::now();
            foreach ($losDemas as $index => $esperando) {
                $nuevaHora = $horaBase->copy()->addMinutes($minutosPorTurno * ($index + 1));
                $esperando->update(['hora_estimada' => $nuevaHora->format('H:i:s')]);
            }

            DB::commit();
            return response()->json(['success' => true, 'mensaje' => 'Turno llamado exitosamente']);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // 4b. Admin: Marca un turno como completado y procesa el cobro (Caja o Cuenta Corriente)
    public function completar(Request $request, $id)
    {
        $turnoActual = Pedido::with('detalles.servicio')->findOrFail($id);
        $turnoActual->update(['estado_atencion' => 'completado', 'estado' => 'entregado']);

        $monto           = (float) ($request->input('monto') ?? $turnoActual->total ?? 0);
        $metodoPago      = $request->input('metodo_pago'); // 'efectivo', 'transferencia', 'cuenta_corriente'
        $nombreCliente   = trim($turnoActual->nombre_cliente ?? 'Cliente');
        $telefonoCliente = trim($turnoActual->telefono ?? '');
        $servicioNombre  = $turnoActual->detalles->first()?->servicio?->nombre ?? 'Servicio';
        $localId         = $turnoActual->id_local;

        // 1. Buscar o registrar la Persona cliente en el local
        $persona = null;
        if (!empty($telefonoCliente) && $telefonoCliente !== '0') {
            $persona = \App\Models\Persona::where('id_local', $localId)
                ->where('tipo_persona', 'cliente')
                ->where('telefono', $telefonoCliente)
                ->first();
        }
        if (!$persona && !empty($nombreCliente)) {
            $persona = \App\Models\Persona::where('id_local', $localId)
                ->where('tipo_persona', 'cliente')
                ->where('nombre', $nombreCliente)
                ->first();
        }
        if (!$persona && !empty($nombreCliente)) {
            $persona = \App\Models\Persona::create([
                'tipo_persona' => 'cliente',
                'nombre'       => $nombreCliente,
                'telefono'     => !empty($telefonoCliente) && $telefonoCliente !== '0' ? $telefonoCliente : '',
                'mail'         => $request->input('email', ''),
                'estado'       => 'Activo',
                'id_local'     => $localId,
            ]);
        }

        // 2. Si el pago es "A Cuenta" (cuenta corriente) -> registrar Ingreso con saldo pendiente
        if ($metodoPago === 'cuenta_corriente' && $monto > 0) {
            \App\Models\Ingreso::create([
                'idpersona'   => $persona?->idpersona,
                'monto'       => $monto,
                'tipo_pago'   => 'cuenta_corriente',
                'descripcion' => "Servicio a cuenta: {$servicioNombre}" . ($persona ? " - {$persona->nombre}" : ""),
                'saldo'       => $monto,
                'estado'      => 'activo',
                'id_local'    => $localId,
            ]);
        } elseif ($monto > 0 && !empty($metodoPago)) {
            // 3. Si se cobró por Efectivo / Transferencia -> registrar Ingreso en caja (saldo = 0, ya cobrado)
            \App\Models\Ingreso::create([
                'idpersona'   => $persona?->idpersona,
                'monto'       => $monto,
                'tipo_pago'   => in_array($metodoPago, ['efectivo', 'transferencia', 'debito', 'credito']) ? $metodoPago : 'efectivo',
                'descripcion' => "Cobro de turno: {$servicioNombre}" . ($persona ? " - {$persona->nombre}" : ""),
                'saldo'       => 0,
                'estado'      => 'activo',
                'id_local'    => $localId,
            ]);
        }

        // 4. ¿El peluquero eligió crear cuenta de usuario para el cliente?
        if ($request->input('crear_cuenta') && $request->input('email')) {
            $email = strtolower(trim($request->input('email')));
            $telefono = preg_replace('/[^\d]/', '', $turnoActual->telefono ?? '12345678');
            
            $user = \App\Models\User::where('email', $email)->first();
            
            if (!$user) {
                $user = \App\Models\User::create([
                    'name'     => $turnoActual->nombre_cliente ?? 'Cliente',
                    'email'    => $email,
                    'password' => \Illuminate\Support\Facades\Hash::make($telefono),
                    'role_id'  => 2,
                ]);
            }
            
            if ($persona) {
                $persona->update(['user_id' => $user->id, 'mail' => $email]);
            }
        }

        return response()->json(['success' => true, 'mensaje' => 'Turno completado exitosamente']);
    }

    public function cancelar($id)
    {
        $turno = Pedido::findOrFail($id);
        $turno->update([
            'estado_atencion' => 'ausente',
            'estado_reserva' => 'cancelada',
            'estado' => 'cancelado'
        ]);
        return response()->json(['success' => true]);
    }

    public function cancelarPublico($token)
    {
        $turno = Pedido::where('token_publico', $token)->firstOrFail();
        $turno->update([
            'estado_atencion' => 'ausente',
            'estado_reserva' => 'cancelada',
            'estado' => 'cancelado'
        ]);
        return response()->json(['success' => true]);
    }

    // 5. Admin: Ver la cola de hoy
    public function adminQueue(Request $request)
    {
        $user = auth('sanctum')->user();
        $local = \App\Models\Local::where('id_user', $user->id)->first();
        $localId = $local ? $local->id : $request->query('local_id');

        // Solución al bug de medianoche UTC: usar la fecha local correcta
        $fechaHoy = $request->query('fecha', \Carbon\Carbon::now('America/Argentina/Buenos_Aires')->toDateString());

        $pedidos = Pedido::with('detalles.servicio', 'detalles.recurso')
            ->where('tipo_pedido', 'servicio')
            ->where('id_local', $localId)
            ->where('fecha_servicio', '>=', \Carbon\Carbon::now('America/Argentina/Buenos_Aires')->subDays(30)->toDateString())
            ->orderBy('fecha_servicio', 'desc')
            ->orderByRaw('-posicion_cola DESC') // Pone los nulls al final
            ->orderBy('hora_inicio', 'asc')
            ->orderBy('hora_estimada', 'asc')
            ->get();
            
        $result = $pedidos->map(function ($p) {
            $detalle = $p->detalles->first();
            return [
                'id' => $p->id,
                'nombre_cliente' => $p->nombre_cliente,
                'posicion_cola' => $p->posicion_cola,
                'slot_hora' => $p->hora_inicio, 
                'hora_estimada' => $p->hora_estimada,
                'estado_atencion' => $p->estado_atencion,
                'fecha_servicio' => $p->fecha_servicio,
                'servicio' => $detalle ? ($detalle->servicio->nombre ?? 'Servicio') : 'Servicio',
                'precio' => $detalle && $detalle->servicio ? $detalle->servicio->precio : 0,
                'recurso' => $detalle ? ($detalle->recurso->nombre ?? null) : null,
                'tipo_reserva' => $detalle && $detalle->servicio ? $detalle->servicio->tipo_reserva : 'cola_virtual'
            ];
        });

        return response()->json($result);
    }

    // 6. Admin: Obtener el estado actual del local (si está aceptando turnos)
    public function getEstadoLocal()
    {
        $user = auth('sanctum')->user();
        $local = \App\Models\Local::where('id_user', $user->id)->first();
        if (!$local) return response()->json(['disponible' => false]);
        
        // Usamos el campo de_turno como interruptor maestro de turnos para locales de servicio
        // Por defecto asumimos 1 si es null
        $disponible = isset($local->de_turno) ? (bool)$local->de_turno : true;
        return response()->json(['disponible' => $disponible]);
    }

    // 7. Admin: Alternar el switch de disponibilidad
    public function toggleEstadoLocal()
    {
        $user = auth('sanctum')->user();
        $local = \App\Models\Local::where('id_user', $user->id)->first();
        if (!$local) return response()->json(['success' => false], 404);
        
        $estadoActual = isset($local->de_turno) ? (bool)$local->de_turno : true;
        $local->de_turno = !$estadoActual;
        $local->save();

        return response()->json(['success' => true, 'disponible' => $local->de_turno]);
    }

    // 8. Admin: Listar bloqueos de horarios de una fecha
    public function getBloqueos(Request $request)
    {
        $user = auth('sanctum')->user();
        $local = \App\Models\Local::where('id_user', $user->id)->firstOrFail();
        $fecha = $request->query('fecha', Carbon::now('America/Argentina/Buenos_Aires')->toDateString());

        $bloqueos = \App\Models\BloqueoHorario::with('servicio')
            ->where('id_local', $local->id)
            ->whereDate('fecha', $fecha)
            ->orderBy('hora_inicio', 'asc')
            ->get();

        return response()->json(['bloqueos' => $bloqueos]);
    }

    // 9. Admin: Crear un bloqueo de horario para inhabilitar un slot o rango
    public function crearBloqueo(Request $request)
    {
        $request->validate([
            'fecha'       => 'required|date',
            'hora_inicio' => 'required|string',
            'hora_fin'    => 'nullable|string',
            'duracion'    => 'nullable|integer',
            'idservicio'  => 'nullable|integer',
            'motivo'      => 'nullable|string|max:255',
        ]);

        $user  = auth('sanctum')->user();
        $local = \App\Models\Local::where('id_user', $user->id)->firstOrFail();

        $horaInicio = trim($request->hora_inicio);
        $horaFin    = trim($request->hora_fin ?? '');

        if (empty($horaFin)) {
            $dur = (int) ($request->input('duracion') ?: 30);
            $horaFin = Carbon::parse($request->fecha . ' ' . $horaInicio, 'America/Argentina/Buenos_Aires')
                ->addMinutes($dur)
                ->format('H:i');
        }

        $bloqueo = \App\Models\BloqueoHorario::create([
            'id_local'    => $local->id,
            'idservicio'  => $request->idservicio ?: null,
            'fecha'       => $request->fecha,
            'hora_inicio' => strlen($horaInicio) === 5 ? $horaInicio . ':00' : $horaInicio,
            'hora_fin'    => strlen($horaFin) === 5 ? $horaFin . ':00' : $horaFin,
            'motivo'      => $request->motivo ?: 'Inhabilitado por administrador',
        ]);

        return response()->json([
            'success' => true,
            'mensaje' => 'Horario inhabilitado correctamente',
            'bloqueo' => $bloqueo->load('servicio')
        ], 201);
    }

    // 10. Admin: Eliminar / Habilitar un horario previamente bloqueado
    public function eliminarBloqueo(Request $request, $id)
    {
        $user  = auth('sanctum')->user();
        $local = \App\Models\Local::where('id_user', $user->id)->firstOrFail();

        $bloqueo = \App\Models\BloqueoHorario::where('id_local', $local->id)->findOrFail($id);
        $bloqueo->delete();

        return response()->json([
            'success' => true,
            'mensaje' => 'Horario habilitado nuevamente con éxito'
        ]);
    }
}
