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

            // Reservas existentes
            $reservas = Pedido::where('tipo_pedido', 'servicio')
                ->whereDate('fecha_servicio', $fecha)
                ->whereIn('estado_atencion', ['en_espera', 'siendo_atendido', 'atendido'])
                ->whereHas('detalles', function ($q) use ($servicioId, $recursoId) {
                    $q->where('idservicio', $servicioId);
                    if ($recursoId) {
                        $q->where('recurso_id', $recursoId);
                    }
                })
                ->pluck('hora_inicio')
                ->filter()
                ->toArray();
                
            $horasOcupadas = array_map(function($h) { return Carbon::parse($h)->format('H:i'); }, $reservas);

            $ahora = Carbon::now('America/Argentina/Buenos_Aires');
            $esHoy = Carbon::parse($fecha, 'America/Argentina/Buenos_Aires')->isToday();

            foreach ($turnosDelDia as $turno) {
                if (empty($turno['apertura']) || empty($turno['cierre'])) continue;

                $inicioTurno = Carbon::parse($fecha . ' ' . $turno['apertura'], 'America/Argentina/Buenos_Aires');
                $finTurno = Carbon::parse($fecha . ' ' . $turno['cierre'], 'America/Argentina/Buenos_Aires');
                $currentSlot = clone $inicioTurno;

                while ($currentSlot->copy()->addMinutes($duracion)->lte($finTurno)) {
                    $horaStr = $currentSlot->format('H:i');
                    $esFuturo = !$esHoy || $currentSlot->gt($ahora);
                    
                    if ($esFuturo && !in_array($horaStr, $horasOcupadas)) {
                        $slots[] = [
                            'hora' => $horaStr,
                            'disponible' => true
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
            'horasOcupadas' => $horasOcupadas ?? [],
        ];

        \Illuminate\Support\Facades\Log::info('Turno fijo debug:', $debug);

        return response()->json([
            'tipo' => 'turno_fijo',
            'slots' => $slots,
            'cerrado' => $cerrado,
            'debug' => $debug
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

    // 4b. Admin: Marca un turno como completado
    public function completar(Request $request, $id)
    {
        $turnoActual = Pedido::findOrFail($id);
        $turnoActual->update(['estado_atencion' => 'completado', 'estado' => 'entregado']);

        // ¿El peluquero eligió crear cuenta para el cliente?
        if ($request->input('crear_cuenta') && $request->input('email')) {
            $email = strtolower(trim($request->input('email')));
            $telefono = preg_replace('/[^\d]/', '', $turnoActual->telefono ?? '12345678');
            
            // 1. Verificar si ya existe en users
            $user = \App\Models\User::where('email', $email)->first();
            
            if (!$user) {
                // Crear usuario con rol de cliente (2) y el teléfono como password
                $user = \App\Models\User::create([
                    'name' => $turnoActual->nombre_cliente ?? 'Cliente',
                    'email' => $email,
                    'password' => \Illuminate\Support\Facades\Hash::make($telefono),
                    'role_id' => 2,
                ]);
            }
            
            // 2. Vincular como cliente (Persona) en el local si no lo está
            $persona = \App\Models\Persona::where('id_local', $turnoActual->id_local)
                ->where('user_id', $user->id)
                ->first();
                
            if (!$persona) {
                \App\Models\Persona::create([
                    'tipo_persona' => 'cliente',
                    'nombre' => $turnoActual->nombre_cliente ?? 'Cliente',
                    'telefono' => $turnoActual->telefono,
                    'mail' => $email,
                    'id_local' => $turnoActual->id_local,
                    'user_id' => $user->id,
                    'estado' => 'activo'
                ]);
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
}
