<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Servicio;
use App\Models\Persona;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ServicioController extends Controller
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
        $user = Auth::user();
        $localId = $this->getLocalId();

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
        $localId = $this->getLocalId();

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio' => 'required|numeric|min:0',
            'tipo_servicio' => 'required|string|in:plan,clase,servicio_general',
            'duracion_dias' => 'nullable|integer|min:1',
            'creditos' => 'nullable|integer|min:1',
            'estado' => 'boolean',
            'duracion' => 'nullable|integer|min:1',
            'buffer_tiempo' => 'nullable|integer|min:0',
            'tipo_reserva' => 'nullable|string|in:sin_reserva,coordinacion,turno_fijo,cola_virtual',
            'es_reservable' => 'nullable|boolean',
            'mostrar_feed' => 'nullable|boolean',
            'imagen' => 'nullable|image|max:2048'
        ]);

        $estado = $request->input('estado', true);
        
        $imagenPath = null;
        if ($request->hasFile('imagen')) {
            $imagenPath = $request->file('imagen')->store('servicios', 'public');
        }

        $servicio = Servicio::create([
            'id_local' => $localId,
            'nombre' => $validated['nombre'],
            'descripcion' => $validated['descripcion'] ?? null,
            'precio' => $validated['precio'],
            'tipo_servicio' => $validated['tipo_servicio'],
            'duracion_dias' => $validated['duracion_dias'] ?? null,
            'creditos' => $validated['creditos'] ?? null,
            'duracion' => $validated['duracion'] ?? 30,
            'buffer_tiempo' => $validated['buffer_tiempo'] ?? 0,
            'tipo_reserva' => $validated['tipo_reserva'] ?? 'sin_reserva',
            'es_reservable' => $validated['es_reservable'] ?? true,
            'mostrar_feed' => $validated['mostrar_feed'] ?? true,
            'imagen' => $imagenPath,
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
        $localId = $this->getLocalId();
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
        $localId = $this->getLocalId();
        $servicio = Servicio::where('id_local', $localId)->findOrFail($id);

        $validated = $request->validate([
            'nombre' => 'sometimes|string|max:255',
            'descripcion' => 'nullable|string',
            'precio' => 'sometimes|numeric|min:0',
            'duracion_dias' => 'nullable|integer|min:1',
            'creditos' => 'nullable|integer|min:1',
            'estado' => 'sometimes|boolean',
            'duracion' => 'nullable|integer|min:1',
            'buffer_tiempo' => 'nullable|integer|min:0',
            'tipo_reserva' => 'nullable|string|in:sin_reserva,coordinacion,turno_fijo,cola_virtual',
            'es_reservable' => 'nullable|boolean',
            'mostrar_feed' => 'nullable|boolean',
            'imagen' => 'nullable|image|max:2048'
        ]);

        if ($request->hasFile('imagen')) {
            if ($servicio->imagen) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($servicio->imagen);
            }
            $validated['imagen'] = $request->file('imagen')->store('servicios', 'public');
        }

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
        $localId = $this->getLocalId();
        $servicio = Servicio::where('id_local', $localId)->findOrFail($id);

        $servicio->delete();

        return response()->json([
            'message' => 'Servicio eliminado exitosamente'
        ]);
    }
}
