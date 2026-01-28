<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AsistenciaGym;
use App\Models\Persona;
use App\Models\ReservaGym;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Role;

class AsistenciaGymController extends Controller
{
    private function getLocalId()
    {
        $user = Auth::user();
        if (!$user) return null;

        try {
            if ($user->local && isset($user->local->id)) {
                return $user->local->id;
            }
        } catch (\Exception $e) {
        }

        $persona = Persona::where('user_id', $user->id)->first();
        if ($persona) {
            return $persona->id_local;
        }

        return $user->id_local ?? $user->local_id ?? null;
    }

    public function index(Request $request)
    {
        $localId = $this->getLocalId();
        $query = AsistenciaGym::where('id_local', $localId)
            ->with(['persona', 'clase', 'membresia'])
            ->orderBy('fecha_asistencia', 'desc');

        // Si es socio, filtrar solo sus asistencias
        $gymSocioRole = Role::where('name', 'gym_socio')->first();
        $user = Auth::user();
        $isSocio = $gymSocioRole && $user->role_id == $gymSocioRole->id;

        if ($isSocio) {
            $socio = Persona::where('user_id', $user->id)->first();
            if ($socio) {
                $query->where('id_persona', $socio->idpersona);
            } else {
                return response()->json(['asistencias' => []]);
            }
        }

        if ($request->has('fecha')) {
            $query->whereDate('fecha_asistencia', $request->fecha);
        }

        $perPage = $request->input('per_page', 15);
        $asistencias = $query->paginate($perPage);

        $asistencias->through(function ($asistencia) {
            if ($asistencia->persona && $asistencia->persona->foto) {
                $asistencia->persona->foto = preg_match('/^http/', $asistencia->persona->foto)
                    ? $asistencia->persona->foto
                    : url($asistencia->persona->foto);
            }
            return $asistencia;
        });

        return response()->json([
            'asistencias' => $asistencias->items(),
            'meta' => [
                'current_page' => $asistencias->currentPage(),
                'last_page' => $asistencias->lastPage(),
                'per_page' => $asistencias->perPage(),
                'total' => $asistencias->total(),
            ]
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_persona' => 'required|exists:personas,idpersona',
            'id_clase_gym' => 'nullable|exists:clases_gym,id',
            'id_reserva' => 'nullable|exists:reservas_gym,id',
        ]);

        $socio = Persona::findOrFail($validated['id_persona']);
        $membresia = null;

        // 1. Prioridad: Membresía de la reserva
        if ($validated['id_reserva']) {
            $reserva = ReservaGym::find($validated['id_reserva']);
            if ($reserva && $reserva->id_membresia) {
                $membresia = \App\Models\Membresia::find($reserva->id_membresia);
            }
        }

        // 2. Fallback: Membresía activa actual
        if (!$membresia) {
            $membresia = $socio->membresia_activa;
        }

        if (!$membresia) {
            return response()->json(['message' => 'El socio no tiene membresía válida o activa para esta asistencia.'], 403);
        }

        DB::beginTransaction();
        try {
            $fechaAsistencia = now();

            // Si se marca desde una reserva, obtener la fecha de la reserva y la hora de la clase
            if ($request->id_reserva) {
                $reserva = ReservaGym::with('clase')->find($request->id_reserva);
                if ($reserva && $reserva->clase) {
                    $fechaBase = $reserva->fecha_reserva->format('Y-m-d');
                    $horaBase = $reserva->clase->hora_inicio->format('H:i:s');
                    $fechaAsistencia = $fechaBase . ' ' . $horaBase;

                    $reserva->update(['estado' => 'asistio']);
                }
            } elseif ($validated['id_clase_gym']) {
                // Si no hay reserva pero hay clase, usar la hora de la clase para el dia de hoy
                $clase = \App\Models\ClaseGym::find($validated['id_clase_gym']);
                if ($clase) {
                    $fechaBase = date('Y-m-d');
                    $horaBase = $clase->hora_inicio->format('H:i:s');
                    $fechaAsistencia = $fechaBase . ' ' . $horaBase;
                }
            }

            $asistencia = AsistenciaGym::create([
                'id_persona' => $socio->idpersona,
                'id_clase_gym' => $validated['id_clase_gym'] ?? null,
                'id_membresia' => $membresia->id,
                'id_local' => $this->getLocalId(),
                'fecha_asistencia' => $fechaAsistencia,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Asistencia registrada correctamente',
                'asistencia' => $asistencia->load(['persona', 'clase'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al registrar asistencia', 'error' => $e->getMessage()], 500);
        }
    }
}
