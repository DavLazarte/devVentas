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
            'monto_pagado'  => 'required|numeric|min:1',
            'metodo_pago'   => 'required|in:efectivo,transferencia,tarjeta,cuenta',
            'observaciones' => 'nullable|string'
        ]);

        DB::beginTransaction();
        try {
            // 1. Crear el recibo (PagoCuota)
            $pago = PagoCuota::create([
                'id_credito'   => $credito->id,
                'idpersona'    => $credito->idpersona,
                'id_cobrador'  => $user->id,
                'monto_pagado' => $validated['monto_pagado'],
                'fecha_pago'   => now(),
                'metodo_pago'  => $validated['metodo_pago'],
                'observaciones' => $validated['observaciones'] ?? null
            ]);

            // 2. Distribuir el pago en cuotas pendientes/vencidas (orden por nro_cuota ASC)
            $cuotasPendientes = Cuota::where('id_credito', $credito->id)
                ->whereIn('estado', ['pendiente', 'vencida'])
                ->orderBy('nro_cuota', 'asc')
                ->get();

            $saldoPago = (float) $validated['monto_pagado'];
            $totalAplicado = 0;

            foreach ($cuotasPendientes as $cuota) {
                if ($saldoPago <= 0) break;

                $montoCuota = (float) $cuota->monto;

                // Solo marcar como pagada si el saldo cubre la cuota completa (Opción A)
                if ($saldoPago >= $montoCuota) {
                    $pago->cuotas()->attach($cuota->id, [
                        'monto_aplicado' => $montoCuota
                    ]);

                    $cuota->estado     = 'pagada';
                    $cuota->fecha_pago = now();
                    $cuota->save();

                    $saldoPago    -= $montoCuota;
                    $totalAplicado += $montoCuota;
                } else {
                    // El sobrante no alcanza para la siguiente cuota — detenemos
                    break;
                }
            }

            // 3. Descontar del saldo del crédito lo efectivamente aplicado
            $credito->saldo_pendiente -= $totalAplicado;

            if ($credito->saldo_pendiente <= 0) {
                $credito->saldo_pendiente = 0;
                $credito->estado = 'cancelado';
            }
            $credito->save();

            // 4. Registrar Ingreso en Caja
            Ingreso::create([
                'idpersona'   => $credito->idpersona,
                'monto'       => $validated['monto_pagado'],
                'tipo_pago'   => $validated['metodo_pago'],
                'descripcion' => "Cobro Cuotas Crédito #{$credito->id}",
                'saldo'       => 0,
                'estado'      => 'activo',
                'id_local'    => $id_local
            ]);

            DB::commit();

            return response()->json([
                'message'        => 'Pago registrado correctamente',
                'cuotas_pagadas' => $pago->cuotas()->count(),
                'pago'           => $pago->load('cuotas'),
                'credito'        => $credito->load(['cuotas' => fn($q) => $q->orderBy('nro_cuota')])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al registrar el pago',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
