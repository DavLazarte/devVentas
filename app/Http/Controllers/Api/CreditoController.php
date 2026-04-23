<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Credito;
use App\Models\PlanCredito;
use App\Models\Cuota;
use App\Models\PagoCuota;
use App\Models\Salida;
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

        // Si es cobrador, filtra sus créditos
        if ($rol === 'financiera_cobrador') {
            $queryActivos->where('id_cobrador', $user->id);
            $queryMora->where('id_cobrador', $user->id);
        }

        $activos = $queryActivos->count();
        $en_mora = $queryMora->count();

        // Cobros hoy: sumar los PagoCuota del día del local (o del cobrador)
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

        // Si es cobrador, solo ve sus cuentas
        if ($rol === 'financiera_cobrador') {
            $query->where('id_cobrador', $user->id);
        }

        // Filtro por estado
        if ($request->has('estado')) {
            $query->where('estado', $request->estado);
        }

        // Búsqueda por nombre de cliente
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
            'observaciones' => 'nullable|string'
        ]);

        $plan = PlanCredito::findOrFail($validated['id_plan_credito']);

        // Calcular montos y fechas
        $monto_aprobado = $validated['monto_aprobado'];
        // Tasa de interés es un porcentaje. Ej: 30% -> monto * 1.30
        $tasa = $plan->tasa_interes;
        $total_a_pagar = $monto_aprobado * (1 + ($tasa / 100));
        $monto_cuota = $total_a_pagar / $plan->cantidad_cuotas;

        $fechaOtorgamiento = Carbon::parse($validated['fecha_otorgamiento']);
        $fechaPrimerVencimiento = $this->calcularProximoVencimiento($fechaOtorgamiento, $plan->periodicidad);

        DB::beginTransaction();
        try {
            // 1. Crear el Crédito
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
                'observaciones' => $validated['observaciones'] ?? null,
            ]);

            // 2. Generar las Cuotas
            $fechaVtoActual = $fechaPrimerVencimiento->copy();
            
            for ($i = 1; $i <= $plan->cantidad_cuotas; $i++) {
                Cuota::create([
                    'id_credito' => $credito->id,
                    'nro_cuota' => $i,
                    'monto' => $monto_cuota,
                    'monto_mora' => 0,
                    'fecha_vencimiento' => $fechaVtoActual->copy(),
                    'estado' => 'pendiente'
                ]);

                // Calcular próximo vencimiento
                $fechaVtoActual = $this->calcularProximoVencimiento($fechaVtoActual, $plan->periodicidad);
            }

            // 3. Registrar el egreso en Caja (Salida)
            Salida::create([
                'idpersona' => $validated['idpersona'],
                'tipo_salida' => 'credito',
                'monto' => $monto_aprobado,
                'saldo' => $monto_aprobado,
                'descripcion' => "Desembolso Crédito #{$credito->id}",
                'estado' => 'activo',
                'id_local' => $id_local
            ]);

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

        return response()->json(['credito' => $credito]);
    }

    private function calcularProximoVencimiento(Carbon $fechaActual, $periodicidad)
    {
        $fecha = $fechaActual->copy();
        switch ($periodicidad) {
            case 'semanal':
                return $fecha->addWeeks(1);
            case 'quincenal':
                return $fecha->addDays(15);
            case 'mensual':
                return $fecha->addMonths(1);
            default:
                return $fecha;
        }
    }
}
