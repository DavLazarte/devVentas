<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Servicio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ServicioController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $localId = $user->local->id;

        // Default to 'plan' if not specified, or allow filtering
        $tipoServicio = $request->query('tipo_servicio', 'plan');

        $query = Servicio::where('id_local', $localId)
            ->where('tipo_servicio', $tipoServicio);

        if ($request->has('estado')) {
            if ($request->estado === 'activo') {
                $query->where('estado', 1);
            } elseif ($request->estado === 'inactivo') {
                $query->where('estado', 0);
            }
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                    ->orWhere('descripcion', 'like', "%{$search}%");
            });
        }

        $servicios = $query->get();

        return response()->json(['servicios' => $servicios]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $localId = Auth::user()->local->id;

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio' => 'required|numeric|min:0',
            'tipo_servicio' => 'required|string|in:plan,clase,servicio_general',
            'duracion_dias' => 'nullable|integer|min:1',
            'creditos' => 'nullable|integer|min:1',
            'estado' => 'boolean',
            // Add other fields as necessary based on model
        ]);

        // Default visual state via 'estado' boolean
        $estado = $request->input('estado', true);

        $servicio = Servicio::create([
            'id_local' => $localId,
            'nombre' => $validated['nombre'],
            'descripcion' => $validated['descripcion'] ?? null,
            'precio' => $validated['precio'],
            'tipo_servicio' => $validated['tipo_servicio'],
            'duracion_dias' => $validated['duracion_dias'] ?? null,
            'creditos' => $validated['creditos'] ?? null,
            'estado' => $estado,
        ]);

        return response()->json([
            'message' => 'Servicio creado exitosamente',
            'servicio' => $servicio
        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $localId = Auth::user()->local->id;
        $servicio = Servicio::where('id_local', $localId)->findOrFail($id);

        return response()->json(['servicio' => $servicio]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $localId = Auth::user()->local->id;
        $servicio = Servicio::where('id_local', $localId)->findOrFail($id);

        $validated = $request->validate([
            'nombre' => 'sometimes|string|max:255',
            'descripcion' => 'nullable|string',
            'precio' => 'sometimes|numeric|min:0',
            // tipo_servicio usually shouldn't change, but allowed if needed
            'duracion_dias' => 'nullable|integer|min:1',
            'creditos' => 'nullable|integer|min:1',
            'estado' => 'sometimes|boolean',
        ]);

        $servicio->update($validated);

        return response()->json([
            'message' => 'Servicio actualizado exitosamente',
            'servicio' => $servicio
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $localId = Auth::user()->local->id;
        $servicio = Servicio::where('id_local', $localId)->findOrFail($id);

        $servicio->delete();

        return response()->json([
            'message' => 'Servicio eliminado exitosamente'
        ]);
    }
}
