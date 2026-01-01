<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Membresia;
use App\Models\Persona;
use App\Models\Servicio;
use App\Models\PagoGym;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MembresiaController extends Controller
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

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $localId = $this->getLocalId();

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

        $membresias->each(function ($m) {
            $m->append(['saldo_pendiente', 'total_pagado']);
        });

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
        $localId = $this->getLocalId();

        $validated = $request->validate([
            'id_socio' => 'required|exists:personas,idpersona',
            'id_plan' => 'required|exists:servicios,idservicio',
            'fecha_inicio' => 'nullable|date',
            // Campos opcionales para registro de pago atómico
            'registrar_pago' => 'nullable|boolean',
            'metodo_pago' => 'required_if:registrar_pago,true|string',
            'monto_pago' => 'required_if:registrar_pago,true|numeric',
            'observaciones_pago' => 'nullable|string',
        ]);

        $idPlan = $validated['id_plan'];
        $plan = Servicio::findOrFail($idPlan);

        $fechaInicio = $validated['fecha_inicio'] ? Carbon::parse($validated['fecha_inicio']) : Carbon::now();
        $fechaFin = null;
        $creditosTotales = null;
        $creditosRestantes = null;
        $tipo = 'fecha';

        if ($plan->duracion_dias) {
            $fechaFin = $fechaInicio->copy()->addDays($plan->duracion_dias);
        }

        if ($plan->creditos) {
            $tipo = 'creditos';
            $creditosTotales = $plan->creditos;
            $creditosRestantes = $plan->creditos;
        } else {
            $tipo = 'fecha';
        }

        DB::beginTransaction();
        try {
            // Antes de crear, marcamos membresías anteriores del mismo socio como vencidas
            Membresia::where('idpersona', $validated['id_socio'])
                ->where('id_local', $localId)
                ->where('estado', 'activa')
                ->update(['estado' => 'vencida']);

            $membresia = Membresia::create([
                'idpersona' => $validated['id_socio'],
                'idservicio' => $validated['id_plan'],
                'id_local' => $localId,
                'tipo' => $tipo,
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'creditos_totales' => $creditosTotales,
                'creditos_restantes' => $creditosRestantes,
                'monto_total' => $plan->precio,
                'estado' => 'activa',
            ]);

            // Registrar pago si se solicitó
            if ($request->registrar_pago) {
                PagoGym::create([
                    'idpersona' => $validated['id_socio'],
                    'id_membresia' => $membresia->id,
                    'id_local' => $localId,
                    'id_user' => $user->id,
                    'monto' => $validated['monto_pago'],
                    'metodo_pago' => $validated['metodo_pago'],
                    'fecha_pago' => Carbon::now(),
                    'observaciones' => $validated['observaciones_pago'] ?? 'Pago inicial al asignar membresía',
                ]);
            }

            // Sincronizar estado del socio
            $membresia->socio->syncEstadoMembresia();

            DB::commit();

            return response()->json([
                'message' => 'Membresía asignada correctamente' . ($request->registrar_pago ? ' y pago registrado' : ''),
                'membresia' => $membresia->load(['socio', 'plan'])
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al procesar la solicitud', 'error' => $e->getMessage()], 500);
        }
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
        $localId = $this->getLocalId();
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
                $dataToUpdate['fecha_fin'] = $fechaInicio->copy()->addDays($plan->duracion_dias);
            }

            if ($plan->creditos) {
                $dataToUpdate['tipo'] = 'creditos';
                $dataToUpdate['creditos_totales'] = $plan->creditos;
                $dataToUpdate['creditos_restantes'] = $plan->creditos;
            } else {
                $dataToUpdate['tipo'] = 'fecha';
                $dataToUpdate['creditos_totales'] = null;
                $dataToUpdate['creditos_restantes'] = null;
            }
        }

        // Mapear id_socio a idpersona si viene
        if ($request->has('id_socio')) {
            $dataToUpdate['idpersona'] = $request->id_socio;
        }

        // Validar que los créditos restantes no superen el total
        if (isset($dataToUpdate['creditos_restantes'])) {
            // Usar el nuevo total si se recalculó, o el existente si no
            $maxCreditos = $dataToUpdate['creditos_totales'] ?? $membresia->creditos_totales;
            if ($maxCreditos !== null && $dataToUpdate['creditos_restantes'] > $maxCreditos) {
                $dataToUpdate['creditos_restantes'] = $maxCreditos;
            }
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
        $localId = $this->getLocalId();
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
