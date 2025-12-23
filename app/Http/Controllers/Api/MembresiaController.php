<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Membresia;
use App\Models\Persona;
use App\Models\Servicio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MembresiaController extends Controller
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

        $query = Membresia::with(['socio', 'plan'])
            ->where('id_local', $localId);

        if ($request->has('id_socio')) {
            $query->where('idpersona', $request->id_socio);
        }

        if ($request->has('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->whereHas('socio', function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%");
            });
        }

        $membresias = $query->orderBy('created_at', 'desc')->get();

        return response()->json(['membresias' => $membresias]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $localId = $user->local->id;

        $validated = $request->validate([
            'id_socio' => 'required|exists:personas,idpersona',
            'id_plan' => 'required|exists:servicios,idservicio',
            'fecha_inicio' => 'nullable|date',
        ]);

        $idPlan = $validated['id_plan'];
        $plan = Servicio::findOrFail($idPlan);

        $fechaInicio = $validated['fecha_inicio'] ? Carbon::parse($validated['fecha_inicio']) : Carbon::now();
        $fechaFin = null;
        $creditosTotales = null;
        $creditosRestantes = null;
        $tipo = 'fecha';

        if ($plan->duracion_dias) {
            $tipo = 'fecha';
            $fechaFin = $fechaInicio->copy()->addDays($plan->duracion_dias);
        } elseif ($plan->creditos) {
            $tipo = 'creditos';
            $creditosTotales = $plan->creditos;
            $creditosRestantes = $plan->creditos;
            // Opcionalmente, algunos gimnasios ponen vencimiento a los créditos, por ahora lo dejamos null según el modelo.
        }

        // Antes de crear, podrías marcar membresías anteriores del mismo socio como inactivas/vencidas
        // Eso depende de la lógica del negocio. Por ahora creamos la nueva como activa.

        $membresia = Membresia::create([
            'idpersona' => $validated['id_socio'],
            'idservicio' => $validated['id_plan'],
            'id_local' => $localId,
            'tipo' => $tipo,
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'creditos_totales' => $creditosTotales,
            'creditos_restantes' => $creditosRestantes,
            'estado' => 'activa',
        ]);

        // Sincronizar estado del socio
        $membresia->socio->syncEstadoMembresia();

        return response()->json([
            'message' => 'Membresía asignada correctamente',
            'membresia' => $membresia->load(['socio', 'plan'])
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
        $membresia = Membresia::with(['socio', 'plan'])
            ->where('id_local', $localId)
            ->findOrFail($id);

        return response()->json(['membresia' => $membresia]);
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
        $membresia = Membresia::where('id_local', $localId)->findOrFail($id);

        $validated = $request->validate([
            'id_plan' => 'sometimes|exists:servicios,idservicio',
            'id_socio' => 'sometimes|exists:personas,idpersona',
            'fecha_inicio' => 'sometimes|date',
            'fecha_fin' => 'nullable|date',
            'creditos_restantes' => 'nullable|integer',
            'estado' => 'sometimes|string|in:activa,vencida,por_vencer,cancelada',
        ]);

        $dataToUpdate = $validated;

        // Si cambia el plan o la fecha de inicio, recalculamos
        if ($request->has('id_plan') || $request->has('fecha_inicio')) {
            $idPlan = $request->input('id_plan', $membresia->idservicio);
            $plan = Servicio::findOrFail($idPlan);

            $fechaInicio = Carbon::parse($request->input('fecha_inicio', $membresia->fecha_inicio));

            $dataToUpdate['idservicio'] = $idPlan;
            $dataToUpdate['fecha_inicio'] = $fechaInicio;

            if ($plan->duracion_dias) {
                $dataToUpdate['tipo'] = 'fecha';
                $dataToUpdate['fecha_fin'] = $fechaInicio->copy()->addDays($plan->duracion_dias);
                $dataToUpdate['creditos_totales'] = null;
                $dataToUpdate['creditos_restantes'] = null;
            } elseif ($plan->creditos) {
                $dataToUpdate['tipo'] = 'creditos';
                $dataToUpdate['creditos_totales'] = $plan->creditos;
                $dataToUpdate['creditos_restantes'] = $plan->creditos;
                $dataToUpdate['fecha_fin'] = null;
            }
        }

        // Mapear id_socio a idpersona si viene
        if ($request->has('id_socio')) {
            $dataToUpdate['idpersona'] = $request->id_socio;
        }

        $membresia->update($dataToUpdate);

        // Sincronizar estado del socio
        $socio = $membresia->socio;
        if ($socio) {
            $socio->syncEstadoMembresia();
        }

        return response()->json([
            'message' => 'Membresía actualizada correctamente',
            'membresia' => $membresia->load(['socio', 'plan'])
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
        $membresia = Membresia::where('id_local', $localId)->findOrFail($id);
        $socio = $membresia->socio;

        $membresia->delete();

        // Sincronizar estado del socio después de eliminar
        if ($socio) {
            $socio->syncEstadoMembresia();
        }

        return response()->json([
            'message' => 'Membresía eliminada correctamente'
        ]);
    }
}
