<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PlanCredito;
use Illuminate\Support\Facades\Auth;

class PlanCreditoController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $id_local = $user->id_local ?? $user->local?->id;

        $planes = PlanCredito::where('id_local', $id_local)->get();

        return response()->json(['planes' => $planes]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        
        // Solo el owner o admin puede crear planes (podrías usar Gate/Policy)
        if (!in_array($user->role->name ?? '', ['admin', 'financiera_owner'])) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $id_local = $user->id_local ?? $user->local?->id;

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'periodicidad' => 'required|in:semanal,quincenal,mensual,unica',
            'cantidad_cuotas' => 'required|integer|min:1',
            'tasa_interes' => 'required|numeric|min:0',
            'mora_diaria' => 'required|numeric|min:0',
            'dias_gracia' => 'nullable|integer|min:0',
            'estado' => 'boolean'
        ]);

        $validated['id_local'] = $id_local;
        $validated['dias_gracia'] = $validated['dias_gracia'] ?? 0;
        $validated['estado'] = $validated['estado'] ?? true;

        $plan = PlanCredito::create($validated);

        return response()->json([
            'message' => 'Plan de crédito creado correctamente',
            'plan' => $plan
        ], 201);
    }

    public function show($id)
    {
        $plan = PlanCredito::findOrFail($id);
        return response()->json(['plan' => $plan]);
    }

    public function update(Request $request, $id)
    {
        $user = $request->user();
        if (!in_array($user->role->name ?? '', ['admin', 'financiera_owner'])) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $plan = PlanCredito::findOrFail($id);

        $validated = $request->validate([
            'nombre' => 'sometimes|string|max:255',
            'periodicidad' => 'sometimes|in:semanal,quincenal,mensual,unica',
            'cantidad_cuotas' => 'sometimes|integer|min:1',
            'tasa_interes' => 'sometimes|numeric|min:0',
            'mora_diaria' => 'sometimes|numeric|min:0',
            'dias_gracia' => 'nullable|integer|min:0',
            'estado' => 'boolean'
        ]);

        $plan->update($validated);

        return response()->json([
            'message' => 'Plan actualizado',
            'plan' => $plan
        ]);
    }

    public function destroy($id)
    {
        $user = request()->user();
        if (!in_array($user->role->name ?? '', ['admin', 'financiera_owner'])) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $plan = PlanCredito::findOrFail($id);
        $plan->delete();

        return response()->json(['message' => 'Plan eliminado']);
    }
}
