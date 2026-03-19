<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClaseGym;
use App\Models\ReservaGym;
use App\Models\AsistenciaGym;
use App\Models\Persona;
use App\Models\Membresia;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class ReservaGymController extends Controller
{
    /**
     * Obtener el ID del local actual de forma robusta.
     */
    private function getLocalId()
    {
        $user = Auth::user();
        if (!$user) return null;

        // 1. Intentar desde relación local (Voyager)
        try {
            if ($user->local && isset($user->local->id)) {
                return $user->local->id;
            }
        } catch (\Exception $e) {
        }

        // 2. Intentar desde Persona vinculada
        $persona = Persona::where('user_id', $user->id)->first();
        if ($persona) {
            return $persona->id_local;
        }

        // 3. Fallback a campos directos
        return $user->id_local ?? $user->local_id ?? null;
    }

    /**
     * Listar reservas.
     */
    public function index(Request $request)
    {
        $localId = $this->getLocalId();
        $user = Auth::user();

        $query = ReservaGym::with(['clase', 'persona', 'membresia'])
            ->where('id_local', $localId);

        // Si es socio, forzar el filtro por su propia persona
        $gymSocioRole = Role::where('name', 'gym_socio')->first();
        $isSocio = $gymSocioRole && $user->role_id == $gymSocioRole->id;

        if ($isSocio) {
            $socio = Persona::where('user_id', $user->id)->first();
            if ($socio) {
                $query->where('id_persona', $socio->idpersona);
            } else {
                return response()->json(['reservas' => []]);
            }
        } elseif ($request->has('id_persona')) {
            // Solo si no es socio (ej: admin) permitimos filtrar por id_persona arbitrario
            $query->where('id_persona', $request->id_persona);
        }

        if ($request->has('fecha')) {
            $query->whereDate('fecha_reserva', $request->fecha);
        }

        $reservas = $query->orderBy('fecha_reserva', 'desc')->get();

        return response()->json(['reservas' => $reservas]);
    }

    /**
     * Obtener clases disponibles (instancias) para un rango de fechas.
     */
    public function getAvailableClasses(Request $request)
    {
        $localId = $this->getLocalId();
        $user = Auth::user();

        $startDate = $request->query('start_date', now()->format('Y-m-d'));
        $endDate = $request->query('end_date', $startDate); // Por defecto solo el día solicitado (hoy)

        $clasesTemplates = ClaseGym::where('id_local', $localId)
            ->where('estado', 'activa')
            ->with('coach')
            ->get();

        // Si es socio (cliente, no instructor), validamos que no intente ver más allá de mañana por seguridad
        $gymSocioRole = Role::where('name', 'gym_socio')->first();
        $isSocio = $gymSocioRole && $user->role_id == $gymSocioRole->id;
        $isCliente = false; // Flag para diferenciar cliente de instructor

        if ($isSocio) {
            // Verificar si es un cliente real (no instructor)
            $persona = Persona::where('user_id', $user->id)->first();
            $isCliente = $persona && $persona->tipo_persona === 'cliente';

            if ($isCliente) {
                $tomorrow = Carbon::now()->addDay()->endOfDay();
                if (Carbon::parse($startDate)->gt($tomorrow) || Carbon::parse($endDate)->gt($tomorrow)) {
                    return response()->json(['message' => 'Solo puedes ver clases hasta el día de mañana.'], 403);
                }
            }
            // Los instructores no tienen restricción de fechas
        }

        $instancias = [];
        $carbonStart = Carbon::parse($startDate);
        $carbonEnd = Carbon::parse($endDate);

        // Obtener todas las reservas en el rango para el local
        $todasLasReservas = ReservaGym::where('id_local', $localId)
            ->whereBetween('fecha_reserva', [$startDate, $endDate])
            ->with(['persona.asistencias', 'persona.membresias.plan'])
            ->get();

        $inscritosMap = [];
        $reservasSet = []; // Para el usuario actual
        $socioActual = null;

        if ($isSocio) {
            $socioActual = Persona::where('user_id', $user->id)->first();
        }

        foreach ($todasLasReservas as $reserva) {
            $dateStr = Carbon::parse($reserva->fecha_reserva)->format('Y-m-d');
            $key = $dateStr . '|' . $reserva->id_clase_gym;

            // Agregar nombre del alumno al mapa
            if (!isset($inscritosMap[$key])) {
                $inscritosMap[$key] = [
                    'count' => 0,
                    'alumnos' => []
                ];
            }
            $inscritosMap[$key]['count']++;

            // Fix URL foto
            $foto = $reserva->persona->foto;
            if ($foto) {
                $foto = preg_match('/^http/', $foto) ? $foto : url($foto);
            }

            $membresiaActiva = $reserva->persona->membresia_activa;

            $inscritosMap[$key]['alumnos'][] = [
                'id' => $reserva->id,
                'id_persona' => $reserva->id_persona,
                'dni' => $reserva->persona->dni_cuit,
                // 'racha_actual' => $reserva->persona->racha_actual,
                'membresia' => $membresiaActiva ? ['id' => $membresiaActiva->id, 'nombre' => $membresiaActiva->nombre] : null,
                'nombre' => $reserva->persona->nombre,
                'foto' => $foto,
                'tipo_persona' => $reserva->persona->tipo_persona,
                'estado_asistencia' => $reserva->estado
            ];

            // Si es la reserva del usuario actual (si es socio), guardamos su ID
            if ($isSocio && $socioActual && $reserva->id_persona == $socioActual->idpersona) {
                $reservasSet[$key] = $reserva->id;
            }
        }

        // Traducir nombres de días al español para coincidir con el campo dias_semana
        $diasTraduccion = [
            'Monday' => 'Lunes',
            'Tuesday' => 'Martes',
            'Wednesday' => 'Miércoles',
            'Thursday' => 'Jueves',
            'Friday' => 'Viernes',
            'Saturday' => 'Sábado',
            'Sunday' => 'Domingo',
        ];

        $now = Carbon::now();

        // El rango para socios ya fue ajustado al inicio de la función

        $addedTemplates = []; // Para trackear qué clases ya mostramos al socio

        for ($date = $carbonStart->copy(); $date->lte($carbonEnd); $date->addDay()) {
            $diaNombre = $diasTraduccion[$date->format('l')];

            foreach ($clasesTemplates as $template) {
                // Eliminamos la restricción de addedTemplates para que se vean todas las clases del día/mañana

                $diasClase = explode(',', $template->dias_semana);

                if (in_array($diaNombre, $diasClase)) {
                    $dateStr = $date->format('Y-m-d');
                    $key = $dateStr . '|' . $template->id;

                    // Crear datetime completo de la clase
                    $horaInicio = Carbon::parse($template->hora_inicio)->format('H:i:s');
                    $claseDateTime = Carbon::parse($dateStr . ' ' . $horaInicio);

                    // Para socios CLIENTES, mostramos solo clases futuras o de las últimas 12 horas
                    // Los instructores pueden ver historial completo
                    if ($isCliente && $claseDateTime->lt(now()->subHours(12))) {
                        continue;
                    }

                    $inscritosData = $inscritosMap[$key] ?? ['count' => 0, 'alumnos' => []];
                    $enrolled = $inscritosData['count'];
                    $reservaId = $reservasSet[$key] ?? null;

                    $fechaReserva = Carbon::parse($dateStr);
                    $horaInicioTemplate = Carbon::parse($template->hora_inicio);

                    $inicioClase = Carbon::create(
                        $fechaReserva->year,
                        $fechaReserva->month,
                        $fechaReserva->day,
                        $horaInicioTemplate->hour,
                        $horaInicioTemplate->minute,
                        0
                    );

                    // Calcular si se puede reservar según el límite de tiempo
                    $puedeReservar = true;
                    if ($template->minutos_limite_reserva && $template->minutos_limite_reserva > 0) {
                        $tiempoRestante = now()->diffInMinutes($inicioClase, false);
                        $puedeReservar = $tiempoRestante >= $template->minutos_limite_reserva;
                    }

                    $instancias[] = [
                        'id' => $template->id . '_' . $dateStr, // ID único para el front
                        'clase_id' => $template->id,
                        'reserva_id' => $reservaId,
                        'nombre' => $template->nombre,
                        'instructor' => $template->coach->nombre,
                        'fecha' => $dateStr,
                        'hora' => Carbon::parse($template->hora_inicio)->format('H:i'),
                        'duracion' => $template->duracion_minutos,
                        'capacidad' => $template->cupo_maximo,
                        'inscritos' => $enrolled,
                        'alumnos' => $inscritosData['alumnos'],
                        'disponibles' => max(0, $template->cupo_maximo - $enrolled),
                        'reservada' => !is_null($reservaId),
                        'estado_clase' => $template->estado,
                        'puede_reservar' => $puedeReservar,
                        'clase_pasada' => $inicioClase < now(),
                    ];
                }
            }
        }
        // Ordenar por fecha y hora
        usort($instancias, function ($a, $b) {
            return strcmp($a['fecha'] . ' ' . $a['hora'], $b['fecha'] . ' ' . $b['hora']);
        });

        return response()->json(['instancias' => $instancias]);
    }

    /**
     * Crear una reserva.
     */
    public function store(Request $request)
    {
        $localId = $this->getLocalId();
        $user = Auth::user();

        $validated = $request->validate([
            'id_clase_gym' => 'required|exists:clases_gym,id',
            'fecha_reserva' => 'required|date',
            'id_persona' => 'nullable|exists:personas,idpersona', // Solo admin puede pasar esto
        ]);

        // Determinar el socio
        $gymSocioRole = Role::where('name', 'gym_socio')->first();
        $isSocio = $gymSocioRole && $user->role_id == $gymSocioRole->id;

        if ($isSocio) { // Socio
            $socio = Persona::where('user_id', $user->id)->first();
            if (!$socio) {
                return response()->json(['message' => 'No se encontró perfil de socio vinculado.'], 403);
            }

            // Validar que la fecha sea hoy o mañana
            $reservaDate = Carbon::parse($validated['fecha_reserva']);
            if ($reservaDate->isPast() && !$reservaDate->isToday()) {
                return response()->json(['message' => 'No puedes reservar para una fecha pasada.'], 400);
            }
            if ($reservaDate->isAfter(now()->addDay()->endOfDay())) {
                return response()->json(['message' => 'Solo puedes reservar para hoy o mañana.'], 400);
            }
        } else {
            if (!$request->id_persona) {
                return response()->json(['message' => 'Debe especificar un socio.'], 400);
            }
            $socio = Persona::findOrFail($request->id_persona);
        }

        // --- PROTECCIÓN ANTI-DUPLICADO (BLOQUEO ATÓMICO) ---
        $lockKey = 'reserva_lock_' . $socio->idpersona . '_' . $validated['id_clase_gym'] . '_' . $validated['fecha_reserva'];
        $lock = Cache::lock($lockKey, 10); // Bloqueo por 10 segundos

        if (!$lock->get()) {
            return response()->json(['message' => 'Estamos procesando tu reserva. Por favor espera un momento.'], 429);
        }

        try {
            // 1. Verificar si ya tiene una reserva activa para esa clase y fecha
            $existe = ReservaGym::where('id_persona', $socio->idpersona)
                ->where('id_clase_gym', $validated['id_clase_gym'])
                ->whereDate('fecha_reserva', $validated['fecha_reserva'])
                ->where('estado', '!=', 'cancelada')
                ->first();

            if ($existe) {
                return response()->json(['message' => 'Ya tienes una reserva para esta clase.'], 400);
            }

            // 2. Verificar membresía activa (Solo si NO es instructor)
            $isInstructor = $socio->tipo_persona === 'instructor';
            $membresia = $socio->membresia_activa;

            if (!$isInstructor && !$membresia) {
                return response()->json(['message' => 'No tienes una membresía activa para reservar.'], 403);
            }

            // 3. Verificar cupo de la clase
            $clase = ClaseGym::findOrFail($validated['id_clase_gym']);
            $inscritos = ReservaGym::where('id_clase_gym', $clase->id)
                ->whereDate('fecha_reserva', $validated['fecha_reserva'])
                ->where('estado', '!=', 'cancelada')
                ->count();

            if ($inscritos >= $clase->cupo_maximo) {
                return response()->json(['message' => 'La clase ya alcanzó su cupo máximo.'], 400);
            }

            // 4. Validar tiempo mínimo de anticipación para reservar
            if ($clase->minutos_limite_reserva && $clase->minutos_limite_reserva > 0) {
                $fechaReserva = Carbon::parse($validated['fecha_reserva']);
                $horaInicio = Carbon::parse($clase->hora_inicio);

                // Combinar fecha de reserva con hora de inicio de la clase
                $inicioClase = Carbon::create(
                    $fechaReserva->year,
                    $fechaReserva->month,
                    $fechaReserva->day,
                    $horaInicio->hour,
                    $horaInicio->minute,
                    0
                );

                $tiempoRestante = now()->diffInMinutes($inicioClase, false);

                if ($tiempoRestante < $clase->minutos_limite_reserva) {
                    $horasRequeridas = floor($clase->minutos_limite_reserva / 60);
                    $minutosRequeridos = $clase->minutos_limite_reserva % 60;
                    $tiempoTexto = $horasRequeridas > 0
                        ? ($horasRequeridas . ' hora' . ($horasRequeridas > 1 ? 's' : '') . ($minutosRequeridos > 0 ? ' y ' . $minutosRequeridos . ' minutos' : ''))
                        : ($minutosRequeridos . ' minutos');

                    return response()->json([
                        'message' => "Debes reservar con al menos {$tiempoTexto} de anticipación."
                    ], 400);
                }
            }

            DB::beginTransaction();
            try {
                // 4. Si es membresía por créditos, validar y descontar (Solo si NO es instructor)
                if (!$isInstructor && $membresia && $membresia->tipo === 'creditos') {
                    if ($membresia->creditos_restantes <= 0) {
                        return response()->json(['message' => 'No te quedan créditos disponibles.'], 400);
                    }
                    $membresia->decrement('creditos_restantes');
                    $socio->syncEstadoMembresia();
                }

                $reserva = ReservaGym::create([
                    'id_persona' => $socio->idpersona,
                    'id_clase_gym' => $validated['id_clase_gym'],
                    'fecha_reserva' => $validated['fecha_reserva'],
                    'id_membresia' => $isInstructor ? null : $membresia->id,
                    'id_local' => $localId,
                    'estado' => 'reservada'
                ]);

                DB::commit();

                return response()->json([
                    'message' => 'Reserva creada con éxito.',
                    'reserva' => $reserva->load('clase')
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['message' => 'Error al crear la reserva: ' . $e->getMessage()], 500);
            }
        } finally {
            if (isset($lock)) {
                $lock->release();
            }
        }
    }

    /**
     * Cancelar reserva.
     */
    public function destroy($id)
    {
        $reserva = ReservaGym::with('membresia')->findOrFail($id);

        // Reglas de cancelación (ej: solo clientes sobre sus reservas, o administradores)
        // Por simplicidad ahora permitimos si el local coincide
        if ($reserva->id_local != $this->getLocalId()) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $user = Auth::user();
        $gymSocioRole = Role::where('name', 'gym_socio')->first();
        $isSocio = $gymSocioRole && $user->role_id == $gymSocioRole->id;

        if ($isSocio) { // Socio
            $socio = Persona::where('user_id', $user->id)->first();
            if (!$socio || $reserva->id_persona != $socio->idpersona) {
                return response()->json(['message' => 'No puedes cancelar una reserva que no es tuya.'], 403);
            }
        }

        // Validar tiempo mínimo de anticipación para cancelar
        $clase = ClaseGym::find($reserva->id_clase_gym);
        if ($clase && $clase->minutos_limite_cancelacion && $clase->minutos_limite_cancelacion > 0) {
            $fechaReserva = Carbon::parse($reserva->fecha_reserva);
            $horaInicio = Carbon::parse($clase->hora_inicio);

            // Combinar fecha de reserva con hora de inicio de la clase
            $inicioClase = Carbon::create(
                $fechaReserva->year,
                $fechaReserva->month,
                $fechaReserva->day,
                $horaInicio->hour,
                $horaInicio->minute,
                0
            );

            $tiempoRestante = now()->diffInMinutes($inicioClase, false);

            if ($tiempoRestante < $clase->minutos_limite_cancelacion) {
                $horasRequeridas = floor($clase->minutos_limite_cancelacion / 60);
                $minutosRequeridos = $clase->minutos_limite_cancelacion % 60;
                $tiempoTexto = $horasRequeridas > 0
                    ? ($horasRequeridas . ' hora' . ($horasRequeridas > 1 ? 's' : '') . ($minutosRequeridos > 0 ? ' y ' . $minutosRequeridos . ' minutos' : ''))
                    : ($minutosRequeridos . ' minutos');

                return response()->json([
                    'message' => "Debes cancelar con al menos {$tiempoTexto} de anticipación."
                ], 400);
            }
        }

        DB::beginTransaction();
        try {
            // Devolver crédito si corresponde (solo si NO había asistido ya)
            if ($reserva->estado !== 'asistio' && $reserva->membresia->tipo === 'creditos') {
                $maxCreditos = $reserva->membresia->creditos_totales ?? 0;
                if ($reserva->membresia->creditos_restantes < $maxCreditos) {
                    $reserva->membresia->increment('creditos_restantes');
                }
                $reserva->persona->syncEstadoMembresia();
            }

            $reserva->delete();

            DB::commit();
            return response()->json(['message' => 'Reserva eliminada correctamente.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al eliminar reserva'], 500);
        }
    }
}
