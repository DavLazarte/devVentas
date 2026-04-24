<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Credito;
use App\Models\PlanCredito;
use App\Models\Cuota;
use App\Models\PagoCuota;
use App\Models\Salida;
use App\Models\Ingreso;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CreditoController extends Controller
{
    public function dashboard(Request $request)
    {
        $user = $request->user();
        $id_local = $user->id_local ?? $user->local?->id;
        $rol = $user->role->name ?? '';

        $queryActivos = Credito::where('id_local', $id_local)->where('estado', 'activo');
        $queryMora = Credito::where('id_local', $id_local)->where('estado', 'en_mora');

        if ($rol === 'financiera_cobrador') {
            $queryActivos->where('id_cobrador', $user->id);
            $queryMora->where('id_cobrador', $user->id);
        }

        $activos = $queryActivos->count();
        $en_mora = $queryMora->count();

        $queryPagos = PagoCuota::whereDate('fecha_pago', Carbon::today())
            ->whereHas('credito', function($q) use ($id_local) {
                $q->where('id_local', $id_local);
            });
            
        if ($rol === 'financiera_cobrador') {
            $queryPagos->where('id_cobrador', $user->id);
        }

        $cobros_hoy = $queryPagos->sum('monto_pagado');

        return response()->json([
            'activos' => $activos,
            'en_mora' => $en_mora,
            'cobros_hoy' => $cobros_hoy
        ]);
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $id_local = $user->id_local ?? $user->local?->id;
        $rol = $user->role->name ?? '';

        $query = Credito::with(['cliente', 'plan', 'cobrador'])->where('id_local', $id_local);

        if ($rol === 'financiera_cobrador') {
            $query->where('id_cobrador', $user->id);
        }

        // Actualización masiva de mora antes de listar (solo activos que vencieron)
        Credito::where('id_local', $id_local)
            ->where('estado', 'activo')
            ->where('fecha_primer_vencimiento', '<', now()->toDateString())
            ->where('saldo_pendiente', '>', 0)
            ->update(['estado' => 'en_mora']);

        if ($request->has('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->has('search') && $request->search) {
            $s = $request->search;
            $query->whereHas('cliente', function ($q) use ($s) {
                $q->where('nombre', 'like', "%{$s}%");
            });
        }

        $perPage = $request->input('per_page', 10);
        $paginator = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'creditos' => $paginator->items(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'total' => $paginator->total(),
            ]
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $id_local = $user->id_local ?? $user->local?->id;

        $validated = $request->validate([
            'idpersona' => 'required|exists:personas,idpersona',
            'id_plan_credito' => 'required|exists:plan_creditos,id',
            'id_cobrador' => 'nullable|exists:users,id',
            'monto_aprobado' => 'required|numeric|min:1',
            'fecha_otorgamiento' => 'required|date',
            'fecha_primer_vencimiento' => 'required|date',
            'tipo' => 'nullable|in:nuevo,renovado,refinanciado,paralelo',
            'observaciones' => 'nullable|string'
        ]);

        $plan = PlanCredito::findOrFail($validated['id_plan_credito']);

        $monto_aprobado = $validated['monto_aprobado'];
        $tasa = $plan->tasa_interes;
        $total_a_pagar = $monto_aprobado * (1 + ($tasa / 100));
        $monto_cuota = $total_a_pagar / $plan->cantidad_cuotas;

        $fechaOtorgamiento = Carbon::parse($validated['fecha_otorgamiento']);
        $fechaPrimerVencimiento = Carbon::parse($validated['fecha_primer_vencimiento']);

        DB::beginTransaction();
        try {
            $credito = Credito::create([
                'idpersona' => $validated['idpersona'],
                'id_local' => $id_local,
                'id_cobrador' => $validated['id_cobrador'] ?? $user->id,
                'id_plan_credito' => $plan->id,
                'monto_aprobado' => $monto_aprobado,
                'total_a_pagar' => $total_a_pagar,
                'saldo_pendiente' => $total_a_pagar,
                'periodicidad' => $plan->periodicidad,
                'cantidad_cuotas' => $plan->cantidad_cuotas,
                'tasa_aplicada' => $tasa,
                'monto_cuota' => $monto_cuota,
                'fecha_otorgamiento' => $fechaOtorgamiento,
                'fecha_primer_vencimiento' => $fechaPrimerVencimiento,
                'estado' => 'activo',
                'tipo' => $validated['tipo'] ?? 'nuevo',
                'observaciones' => $validated['observaciones'] ?? null,
            ]);

            // Generar cuotas con la FECHA LÍMITE (Vencimiento Final) para todas las cuotas
            // El cliente debe cubrir todas antes de esta fecha
            for ($i = 1; $i <= $plan->cantidad_cuotas; $i++) {
                Cuota::create([
                    'id_credito' => $credito->id,
                    'nro_cuota' => $i,
                    'monto' => $monto_cuota,
                    'monto_mora' => 0,
                    'fecha_vencimiento' => $fechaPrimerVencimiento,
                    'estado' => 'pendiente'
                ]);
            }

            // Registrar el egreso en Caja (Salida) solo si NO es refinanciación
            if (($validated['tipo'] ?? 'nuevo') !== 'refinanciado') {
                Salida::create([
                    'idpersona' => $validated['idpersona'],
                    'tipo_salida' => 'credito',
                    'monto' => $monto_aprobado,
                    'saldo' => $monto_aprobado,
                    'descripcion' => "Desembolso Crédito #{$credito->id} ({$credito->tipo})",
                    'estado' => 'activo',
                    'id_local' => $id_local
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Crédito otorgado exitosamente',
                'credito' => $credito->load('cuotas')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al otorgar el crédito',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $user = request()->user();
        $id_local = $user->id_local ?? $user->local?->id;

        $credito = Credito::with(['cuotas' => function($q) {
            $q->orderBy('nro_cuota', 'asc');
        }, 'cliente', 'cobrador'])->where('id_local', $id_local)->findOrFail($id);

        // Verificación individual de mora al entrar al detalle
        if ($credito->estado === 'activo' && $credito->fecha_primer_vencimiento->isPast() && $credito->saldo_pendiente > 0) {
            $credito->estado = 'en_mora';
            $credito->save();
            $credito->cuotas()->where('estado', 'pendiente')->update(['estado' => 'vencida']);
        }

        return response()->json(['credito' => $credito]);
    }

    public function update(Request $request, $id)
    {
        $user = $request->user();
        $id_local = $user->id_local ?? $user->local?->id;

        $credito = Credito::where('id_local', $id_local)->findOrFail($id);

        // Solo permite editar si no tiene pagos registrados
        $tienePagos = PagoCuota::where('id_credito', $credito->id)->exists();
        if ($tienePagos) {
            return response()->json([
                'message' => 'No se puede editar un crédito que ya tiene pagos registrados.'
            ], 422);
        }

        $validated = $request->validate([
            'id_cobrador' => 'nullable|exists:users,id',
            'observaciones' => 'nullable|string',
        ]);

        $credito->update($validated);

        return response()->json([
            'message' => 'Crédito actualizado',
            'credito' => $credito->fresh()
        ]);
    }

    public function destroy($id)
    {
        $user = request()->user();
        $id_local = $user->id_local ?? $user->local?->id;

        $credito = Credito::where('id_local', $id_local)->findOrFail($id);

        // Solo permite eliminar si no tiene pagos registrados
        $tienePagos = PagoCuota::where('id_credito', $credito->id)->exists();
        if ($tienePagos) {
            return response()->json([
                'message' => 'No se puede eliminar un crédito con pagos registrados. Podés cancelarlo en su lugar.'
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Reintegrar el monto a caja como ingreso (devolución)
            Ingreso::create([
                'idpersona' => $credito->idpersona,
                'monto' => $credito->monto_aprobado, // Solo el capital, sin interés
                'tipo_pago' => 'efectivo',
                'descripcion' => "Reintegro/Anulación Crédito #{$credito->id}",
                'saldo' => 0,
                'estado' => 'activo',
                'id_local' => $id_local
            ]);

            // Eliminar cuotas y crédito
            $credito->cuotas()->delete();
            $credito->delete();

            DB::commit();
            return response()->json(['message' => 'Crédito eliminado y monto reintegrado a caja']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al eliminar el crédito', 'error' => $e->getMessage()], 500);
        }
    }

    public function getCobradores(Request $request)
    {
        $user = $request->user();
        
        // Buscamos el local asociado
        $local = \App\Models\Local::where('id_user', $user->id)->first();
        if (!$local) {
            $persona = \App\Models\Persona::where('user_id', $user->id)->first();
            if ($persona) {
                $local = \App\Models\Local::find($persona->id_local);
            }
        }

        if (!$local) return response()->json(['users' => []]);

        $id_local = $local->id;

        // 1. El dueño del local
        $ownerId = $local->id_user;

        // 2. Otros usuarios vinculados vía la tabla personas (empleados/cobradores)
        $userIdsInLocal = \App\Models\Persona::where('id_local', $id_local)
            ->whereNotNull('user_id')
            ->pluck('user_id')
            ->toArray();

        $userIdsInLocal[] = $ownerId;

        $users = \App\Models\User::whereIn('id', array_unique($userIdsInLocal))
            ->get(['id', 'name']);

        return response()->json(['users' => $users]);
    }

    public function cancelar(Request $request, $id)
    {
        $user = $request->user();
        $id_local = $user->id_local ?? $user->local?->id;

        $credito = Credito::where('id_local', $id_local)->findOrFail($id);

        if (in_array($credito->estado, ['cancelado', 'refinanciado'])) {
            return response()->json(['message' => 'El crédito ya está cerrado.'], 422);
        }

        $credito->estado = 'cancelado';
        $credito->saldo_pendiente = 0;
        $credito->save();

        // Marcar todas las cuotas pendientes como canceladas
        $credito->cuotas()->whereIn('estado', ['pendiente', 'vencida'])->update(['estado' => 'refinanciada']);

        return response()->json([
            'message' => 'Crédito cancelado correctamente',
            'credito' => $credito->fresh(['cuotas', 'cliente'])
        ]);
    }

    public function marcarComoRefinanciado(Request $request, $id)
    {
        $user = $request->user();
        $id_local = $user->id_local ?? $user->local?->id;

        $credito = Credito::where('id_local', $id_local)->findOrFail($id);

        if ($credito->estado === 'refinanciado') {
            return response()->json(['message' => 'El crédito ya fue refinanciado.'], 422);
        }

        $credito->estado = 'refinanciado';
        $credito->saldo_pendiente = 0;
        $credito->save();

        // Marcar cuotas como refinanciadas
        $credito->cuotas()->whereIn('estado', ['pendiente', 'vencida'])->update(['estado' => 'refinanciada']);

        return response()->json([
            'message' => 'Crédito marcado como refinanciado',
            'credito' => $credito->fresh(['cuotas', 'cliente'])
        ]);
    }

    private function calcularProximoVencimiento(Carbon $fechaActual, $periodicidad)
    {
        $fecha = $fechaActual->copy();
        switch ($periodicidad) {
            case 'diaria':
                $fecha->addDay();
                // Saltear domingos
                if ($fecha->dayOfWeek === Carbon::SUNDAY) {
                    $fecha->addDay();
                }
                return $fecha;
            case 'semanal':
                return $fecha->addWeeks(1);
            case 'quincenal':
                return $fecha->addDays(15);
            case 'mensual':
                return $fecha->addMonths(1);
            default:
                return $fecha->addDay();
        }
    }
}
