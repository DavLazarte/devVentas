<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Credito;
use App\Models\Cuota;
use App\Models\PagoCuota;
use App\Models\Ingreso;
use Illuminate\Support\Facades\DB;

class PagoCuotaController extends Controller
{
    public function store(Request $request, $id_credito)
    {
        $user = $request->user();
        $id_local = $user->id_local ?? $user->local?->id;

        $credito = Credito::where('id_local', $id_local)->findOrFail($id_credito);

        $validated = $request->validate([
            'monto_pagado' => 'required|numeric|min:1',
            'metodo_pago' => 'required|in:efectivo,transferencia,tarjeta,cuenta',
            'cuotas' => 'required|array|min:1', // IDs de las cuotas que se están pagando
            'cuotas.*.id' => 'required|exists:cuotas,id',
            'cuotas.*.monto_aplicado' => 'required|numeric|min:1',
            'observaciones' => 'nullable|string'
        ]);

        DB::beginTransaction();
        try {
            // 1. Crear el recibo (PagoCuota)
            $pago = PagoCuota::create([
                'id_credito' => $credito->id,
                'idpersona' => $credito->idpersona,
                'id_cobrador' => $user->id,
                'monto_pagado' => $validated['monto_pagado'],
                'fecha_pago' => now(),
                'metodo_pago' => $validated['metodo_pago'],
                'observaciones' => $validated['observaciones'] ?? null
            ]);

            // 2. Asociar cuotas y actualizar estado de cada una
            $total_aplicado = 0;
            foreach ($validated['cuotas'] as $c) {
                $cuota = Cuota::where('id_credito', $credito->id)->findOrFail($c['id']);
                
                $pago->cuotas()->attach($cuota->id, [
                    'monto_aplicado' => $c['monto_aplicado']
                ]);

                // Asumimos que si se envía un monto aplicado para la cuota, y cubre el monto base (+ mora si tuviese), se marca pagada.
                // Como somos "permisivos", si el frontend manda que se pagó esta cuota, la marcamos como pagada
                $cuota->estado = 'pagada';
                $cuota->fecha_pago = now();
                $cuota->save();

                $total_aplicado += $c['monto_aplicado'];
            }

            // 3. Descontar del saldo del crédito
            // En caso de que el cobrador haya "perdonado" la mora, igual descontamos del saldo original lo que corresponda a la cuota base
            // Para simplificar, descontamos del saldo total el monto que originalmente se esperaba de esas cuotas
            // o descontamos exactamente lo aplicado. Como es permisivo, restemos lo que se pagó realmente.
            $credito->saldo_pendiente -= $total_aplicado;
            
            if ($credito->saldo_pendiente <= 0) {
                $credito->saldo_pendiente = 0;
                $credito->estado = 'cancelado';
            }
            $credito->save();

            // 4. Registrar Ingreso en Caja
            Ingreso::create([
                'idpersona' => $credito->idpersona,
                'monto' => $validated['monto_pagado'],
                'tipo_pago' => $validated['metodo_pago'],
                'descripcion' => "Cobro Cuotas Crédito #{$credito->id}",
                'saldo' => 0, // No aplica directamente acá si es solo cashflow
                'estado' => 'activo',
                'id_local' => $id_local
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Pago registrado correctamente',
                'pago' => $pago->load('cuotas')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al registrar el pago',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
