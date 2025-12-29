<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClaseGym;
use App\Models\ReservaGym;
use App\Models\AsistenciaGym;
use App\Models\Persona;
use App\Models\Membresia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
        if ($user->role_id == 6) {
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

        $instancias = [];
        $carbonStart = Carbon::parse($startDate);
        $carbonEnd = Carbon::parse($endDate);

        // Obtener todas las reservas en el rango para el local
        $todasLasReservas = ReservaGym::where('id_local', $localId)
            ->whereBetween('fecha_reserva', [$startDate, $endDate])
            ->with('persona')
            ->get();

        $inscritosMap = [];
        $reservasSet = []; // Para el usuario actual

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
            $inscritosMap[$key]['alumnos'][] = $reserva->persona->nombre;

            // Si es la reserva del usuario actual (si es socio), guardamos su ID
            $socioActual = null;
            if ($user->role_id == 6) {
                // cache persona to avoid multiple queries
                static $cachedSocio = null;
                if (is_null($cachedSocio)) {
                    $cachedSocio = Persona::where('user_id', $user->id)->first();
                }

                if ($cachedSocio && $reserva->id_persona == $cachedSocio->idpersona) {
                    $reservasSet[$key] = $reserva->id;
                }
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

        for ($date = $carbonStart->copy(); $date->lte($carbonEnd); $date->addDay()) {
            $diaNombre = $diasTraduccion[$date->format('l')];

            foreach ($clasesTemplates as $template) {
                $diasClase = explode(',', $template->dias_semana);

                if (in_array($diaNombre, $diasClase)) {
                    $dateStr = $date->format('Y-m-d');
                    $key = $dateStr . '|' . $template->id;

                    $inscritosData = $inscritosMap[$key] ?? ['count' => 0, 'alumnos' => []];
                    $enrolled = $inscritosData['count'];
                    $reservaId = $reservasSet[$key] ?? null;

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
                        'estado_clase' => $template->estado
                    ];
                }
            }
        }

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
        if ($user->role_id == 6) { // Socio
            $socio = Persona::where('user_id', $user->id)->first();
            if (!$socio) {
                return response()->json(['message' => 'No se encontró perfil de socio vinculado.'], 403);
            }

            // Validar que la fecha sea estrictamente hoy
            if (Carbon::parse($validated['fecha_reserva'])->format('Y-m-d') !== now()->format('Y-m-d')) {
                return response()->json(['message' => 'Solo puedes reservar para el día de hoy.'], 400);
            }
        } else {
            if (!$request->id_persona) {
                return response()->json(['message' => 'Debe especificar un socio.'], 400);
            }
            $socio = Persona::findOrFail($request->id_persona);
        }

        // 1. Verificar si ya tiene una reserva activa para esa clase y fecha
        $existe = ReservaGym::where('id_persona', $socio->idpersona)
            ->where('id_clase_gym', $validated['id_clase_gym'])
            ->whereDate('fecha_reserva', $validated['fecha_reserva'])
            ->where('estado', '!=', 'cancelada')
            ->first();

        if ($existe) {
            return response()->json(['message' => 'Ya tienes una reserva para esta clase.'], 400);
        }

        // 2. Verificar membresía activa
        $membresia = $socio->membresia_activa;
        if (!$membresia) {
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

        DB::beginTransaction();
        try {
            // 4. Si es membresía por créditos, validar y descontar
            if ($membresia->tipo === 'creditos') {
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
                'id_membresia' => $membresia->id,
                'id_local' => $localId,
                'estado' => 'reservada'
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Reserva creada exitosamente',
                'reserva' => $reserva
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al procesar la reserva', 'error' => $e->getMessage()], 500);
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
        if ($user->role_id == 6) { // Socio
            $socio = Persona::where('user_id', $user->id)->first();
            if (!$socio || $reserva->id_persona != $socio->idpersona) {
                return response()->json(['message' => 'No puedes cancelar una reserva que no es tuya.'], 403);
            }
        }

        DB::beginTransaction();
        try {
            // Devolver crédito si corresponde (solo si NO había asistido ya)
            if ($reserva->estado !== 'asistio' && $reserva->membresia->tipo === 'creditos') {
                $reserva->membresia->increment('creditos_restantes');
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

    /**
     * Marcar asistencia.
     */
    public function marcarAsistencia(Request $request)
    {
        $validated = $request->validate([
            'id_persona' => 'required|exists:personas,idpersona',
            'id_clase_gym' => 'nullable|exists:clases_gym,id',
            'id_reserva' => 'nullable|exists:reservas_gym,id',
        ]);

        $socio = Persona::findOrFail($validated['id_persona']);
        $membresia = $socio->membresia_activa;

        if (!$membresia) {
            return response()->json(['message' => 'El socio no tiene membresía activa.'], 403);
        }

        $asistencia = AsistenciaGym::create([
            'id_persona' => $socio->idpersona,
            'id_clase_gym' => $validated['id_clase_gym'] ?? null,
            'id_membresia' => $membresia->id,
            'id_local' => $this->getLocalId(),
            'fecha_asistencia' => now(),
        ]);

        // Si se marca desde una reserva, podríamos actualizar el estado de la reserva
        if ($request->id_reserva) {
            $reserva = ReservaGym::find($request->id_reserva);
            if ($reserva) {
                $reserva->update(['estado' => 'asistio']);
            }
        }

        return response()->json([
            'message' => 'Asistencia registrada correctamente',
            'asistencia' => $asistencia
        ]);
    }
}
