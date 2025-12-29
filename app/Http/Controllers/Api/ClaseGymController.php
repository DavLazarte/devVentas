<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClaseGym;
use App\Models\Persona;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClaseGymController extends Controller
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

    public function index()
    {
        $localId = $this->getLocalId();
        $clases = ClaseGym::where('id_local', $localId)
            ->with(['tipoClase', 'coach', 'local'])
            ->withCount(['reservas as inscritos_hoy' => function ($query) {
                $query->whereDate('fecha_reserva', now());
            }])
            ->get();

        return response()->json(['clases' => $clases]);
    }

    public function store(Request $request)
    {
        $localId = $this->getLocalId();

        $validated = $request->validate([
            'nombre' => 'required|string',
            'idservicio' => 'nullable|exists:servicios,idservicio',
            'id_coach' => 'required|exists:personas,idpersona',
            'dias_semana' => 'required|string', // Ej: "Lunes,Miércoles"
            'hora_inicio' => 'required',
            'hora_fin' => 'required',
            'duracion_minutos' => 'required|integer',
            'cupo_maximo' => 'required|integer',
            'estado' => 'required|in:activa,cancelada',
        ]);

        $clase = ClaseGym::create(array_merge($validated, ['id_local' => $localId]));

        return response()->json([
            'message' => 'Horario de clase creado exitosamente',
            'clase' => $clase->load(['tipoClase', 'coach'])
        ], 201);
    }

    public function show($id)
    {
        $localId = $this->getLocalId();
        $clase = ClaseGym::where('id_local', $localId)
            ->with(['tipoClase', 'coach'])
            ->findOrFail($id);

        return response()->json(['clase' => $clase]);
    }

    public function update(Request $request, $id)
    {
        $localId = $this->getLocalId();
        $clase = ClaseGym::where('id_local', $localId)->findOrFail($id);

        $validated = $request->validate([
            'nombre' => 'sometimes|string',
            'idservicio' => 'sometimes|nullable|exists:servicios,idservicio',
            'id_coach' => 'sometimes|exists:personas,idpersona',
            'dias_semana' => 'sometimes|string',
            'hora_inicio' => 'sometimes',
            'hora_fin' => 'sometimes',
            'duracion_minutos' => 'sometimes|integer',
            'cupo_maximo' => 'sometimes|integer',
            'estado' => 'sometimes|in:activa,cancelada',
        ]);

        $clase->update($validated);

        return response()->json([
            'message' => 'Horario de clase actualizado exitosamente',
            'clase' => $clase->load(['tipoClase', 'coach'])
        ]);
    }

    public function destroy($id)
    {
        $localId = $this->getLocalId();
        $clase = ClaseGym::where('id_local', $localId)->findOrFail($id);

        // Opcional: Verificar si tiene reservas futuras antes de eliminar
        $clase->delete();

        return response()->json(['message' => 'Horario de clase eliminado exitosamente']);
    }
}
