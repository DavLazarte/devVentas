<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SalidaGym;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class SalidaGymController extends Controller
{
    private function getLocalId()
    {
        $user = auth()->user();
        if (!$user) return null;

        try {
            if ($user->local && isset($user->local->id)) {
                return $user->local->id;
            }
        } catch (\Exception $e) {
        }

        $persona = \App\Models\Persona::where('user_id', $user->id)->first();
        if ($persona) {
            return $persona->id_local;
        }

        return $user->id_local ?? $user->local_id ?? null;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $localId = $this->getLocalId();
        $query = SalidaGym::where('id_local', $localId)->with('persona:idpersona,nombre');

        // Filters
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('descripcion', 'like', "%{$search}%")
                    ->orWhere('tipo_salida', 'like', "%{$search}%")
                    ->orWhereHas('persona', function ($q2) use ($search) {
                        $q2->where('nombre', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->has('tipo') && $request->tipo != 'todos') {
            $query->where('tipo_salida', $request->tipo);
        }

        // Date filters
        if ($request->has('fecha_inicio')) {
            $query->whereDate('fecha', '>=', $request->fecha_inicio);
        }

        if ($request->has('fecha_fin')) {
            $query->whereDate('fecha', '<=', $request->fecha_fin);
        }

        // Stats for current month (Filtered by local)
        $currentMonthStart = Carbon::now()->startOfMonth();
        $currentMonthEnd = Carbon::now()->endOfMonth();

        $totalExpenses = SalidaGym::where('id_local', $localId)->sum('monto');
        $monthExpenses = SalidaGym::where('id_local', $localId)
            ->whereBetween('fecha', [$currentMonthStart, $currentMonthEnd])
            ->sum('monto');


        // Pagination
        $perPage = $request->input('per_page', 15);
        $salidas = $query->orderBy('fecha', 'desc')->paginate($perPage);

        return response()->json([
            'salidas' => $salidas->items(),
            'meta' => [
                'current_page' => $salidas->currentPage(),
                'last_page' => $salidas->lastPage(),
                'per_page' => $salidas->perPage(),
                'total' => $salidas->total(),
            ],
            'stats' => [
                'total_historico' => $totalExpenses,
                'total_mes' => $monthExpenses,
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'monto' => 'required|numeric|min:0',
            'descripcion' => 'required|string|max:255',
            'tipo_salida' => 'required|string',
            'fecha' => 'required|date',
            'id_persona' => 'nullable|exists:personas,idpersona',
        ]);

        $localId = $this->getLocalId();
        $validated['id_local'] = $localId;
        $validated['id_usuario'] = auth()->id();

        $salida = SalidaGym::create($validated);

        return response()->json([
            'message' => 'Salida registrada correctamente',
            'salida' => $salida->load('persona:idpersona,nombre')
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $localId = $this->getLocalId();
        $salida = SalidaGym::where('id_local', $localId)->with('persona')->findOrFail($id);
        return response()->json(['salida' => $salida]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $localId = $this->getLocalId();
        $salida = SalidaGym::where('id_local', $localId)->findOrFail($id);

        $validated = $request->validate([
            'monto' => 'numeric|min:0',
            'descripcion' => 'string|max:255',
            'tipo_salida' => 'string',
            'fecha' => 'date',
            'id_persona' => 'nullable|exists:personas,idpersona',
        ]);

        $salida->update($validated);

        return response()->json([
            'message' => 'Salida actualizada correctamente',
            'salida' => $salida->load('persona')
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $localId = $this->getLocalId();
        $salida = SalidaGym::where('id_local', $localId)->findOrFail($id);
        $salida->delete();

        return response()->json(['message' => 'Salida eliminada correctamente']);
    }
}
