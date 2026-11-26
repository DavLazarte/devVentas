<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Persona;
use App\Models\PlanificacionGym;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PlanificacionGymController extends Controller
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

    private function canManage(): bool
    {
        $user = Auth::user();
        if (!$user) return false;

        $roleName = $user->role->name ?? null;
        return in_array($roleName, ['gym_owner', 'superadmin', 'admin'], true) || (int) $user->role_id === 1;
    }

    /**
     * GET /api/planificaciones
     * - ?fecha=YYYY-MM-DD -> devuelve la planificación puntual de ese día
     * - ?desde=YYYY-MM-DD&hasta=YYYY-MM-DD -> devuelve lista de planificaciones del rango
     */
    public function index(Request $request)
    {
        $localId = $this->getLocalId();
        if (!$localId) {
            return response()->json(['message' => 'Sin local activo'], 403);
        }

        if ($request->filled('desde') && $request->filled('hasta')) {
            $items = PlanificacionGym::where('id_local', $localId)
                ->whereBetween('fecha', [$request->query('desde'), $request->query('hasta')])
                ->orderBy('fecha', 'asc')
                ->get()
                ->map(fn($p) => [
                    'id' => $p->id,
                    'fecha' => Carbon::parse($p->fecha)->format('Y-m-d'),
                    'titulo' => $p->titulo,
                    'contenido' => $p->contenido,
                    'updated_at' => $p->updated_at?->toIso8601String(),
                ]);

            return response()->json(['planificaciones' => $items]);
        }

        $fecha = $request->query('fecha', Carbon::today()->format('Y-m-d'));
        $item = PlanificacionGym::where('id_local', $localId)
            ->whereDate('fecha', $fecha)
            ->first();

        return response()->json([
            'planificacion' => $item ? [
                'id' => $item->id,
                'fecha' => Carbon::parse($item->fecha)->format('Y-m-d'),
                'titulo' => $item->titulo,
                'contenido' => $item->contenido,
                'updated_at' => $item->updated_at?->toIso8601String(),
            ] : null,
        ]);
    }

    /**
     * POST /api/planificaciones
     * Crea o actualiza la planificación de una fecha (solo admin / gym_owner).
     */
    public function store(Request $request)
    {
        if (!$this->canManage()) {
            return response()->json(['message' => 'No tienes permisos para modificar la planificación.'], 403);
        }

        $localId = $this->getLocalId();
        if (!$localId) {
            return response()->json(['message' => 'Sin local activo'], 403);
        }

        $validated = $request->validate([
            'fecha' => 'required|date',
            'titulo' => 'nullable|string|max:150',
            'contenido' => 'required|string',
        ]);

        $fecha = Carbon::parse($validated['fecha'])->format('Y-m-d');

        $planificacion = PlanificacionGym::updateOrCreate(
            [
                'id_local' => $localId,
                'fecha' => $fecha,
            ],
            [
                'titulo' => $validated['titulo'] ?? null,
                'contenido' => $validated['contenido'],
                'created_by' => Auth::id(),
            ]
        );

        return response()->json([
            'message' => 'Planificación guardada correctamente.',
            'planificacion' => [
                'id' => $planificacion->id,
                'fecha' => Carbon::parse($planificacion->fecha)->format('Y-m-d'),
                'titulo' => $planificacion->titulo,
                'contenido' => $planificacion->contenido,
                'updated_at' => $planificacion->updated_at?->toIso8601String(),
            ],
        ]);
    }

    /**
     * DELETE /api/planificaciones/{id}
     * Elimina la planificación de un día (solo admin / gym_owner).
     */
    public function destroy($id)
    {
        if (!$this->canManage()) {
            return response()->json(['message' => 'No tienes permisos para eliminar la planificación.'], 403);
        }

        $localId = $this->getLocalId();
        $planificacion = PlanificacionGym::where('id_local', $localId)->findOrFail($id);
        $planificacion->delete();

        return response()->json([
            'message' => 'Planificación eliminada correctamente.',
        ]);
    }
}
