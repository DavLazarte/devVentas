<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AsistenciaGym;
use App\Models\Persona;
use App\Models\ReservaGym;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

        if ($request->has('fecha')) {
            $query->whereDate('fecha_asistencia', $request->fecha);
        }

        $asistencias = $query->get();

        return response()->json(['asistencias' => $asistencias]);
    }

    public function store(Request $request)
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
