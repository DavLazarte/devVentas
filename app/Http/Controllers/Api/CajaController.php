<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ingreso;
use App\Models\Local;
use App\Models\Salida;
use App\Models\Venta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CajaController extends Controller
{
    private function getLocal(): ?Local
    {
        $user = Auth::user();
        if (!$user) return null;
        return Local::where('id_user', $user->id)->first();
    }

    /**
     * GET /api/caja
     * Devuelve ingresos y egresos del día (o la fecha pedida)
     */
    public function index(Request $request)
    {
        $local = $this->getLocal();
        if (!$local) {
            return response()->json(['message' => 'No tenés un local asignado.'], 403);
        }

        $date = $request->has('date') ? $request->date : today()->toDateString();

        // Ventas del día como ingresos automáticos
        $ventas = Venta::with(['detalles.producto', 'detalles.variantesArticulos'])
            ->where('id_local', $local->id)
            ->whereDate('created_at', $date)
            ->get();

        $ventasEntries = $ventas->map(fn($v) => [
            'id'            => 'VENTA-' . $v->id,
            'type'          => 'ingreso',
            'amount'        => (float) $v->pago,       // lo que se cobró (para el balance de caja)
            'totalVenta'    => (float) $v->total_venta, // total real de la venta
            'pago'          => (float) $v->pago,
            'saldo'         => (float) $v->saldo,
            'description'   => 'Venta #' . $v->id,
            'paymentMethod' => $v->forma_de_pago,
            'createdAt'     => $v->created_at->toISOString(),
            'saleId'        => (string) $v->id,
            'items'         => $v->detalles->map(function($d) {
                $variantName = $d->descripcion_variante ?? ($d->variantesArticulos?->descripcion_variante ?? '');
                return [
                    'productName' => ($d->producto?->nombre ?? 'Producto eliminado') . ($variantName ? ' - ' . $variantName : ''),
                    'quantity'    => $d->cantidad_decimal ?? $d->cantidad,
                    'price'       => (float) $d->precio_venta,
                ];
            })->values()->toArray(),
        ]);

        // Ingresos del día (cobros de deuda de clientes)
        $ingresos = Ingreso::where('id_local', $local->id)
            ->whereNotNull('idpersona') // solo cobros de clientes
            ->whereDate('created_at', $date)
            ->get()
            ->map(fn($i) => [
                'id'            => 'ING-' . $i->id_ingreso,
                'type'          => 'ingreso',
                'amount'        => (float) $i->monto,
                'description'   => $i->descripcion ?? 'Cobro de deuda',
                'paymentMethod' => 'cobro_deuda',
                'createdAt'     => $i->created_at?->toISOString(),
                'saleId'        => null,
                'items'         => [],
            ]);

        // Egresos del día
        $salidas = Salida::where('id_local', $local->id)
            ->whereDate('created_at', $date)
            ->get()
            ->map(fn($s) => [
                'id'            => 'SAL-' . $s->idsalida,
                'type'          => 'egreso',
                'amount'        => (float) $s->monto,
                'description'   => $s->descripcion ?? 'Egreso',
                'paymentMethod' => null,
                'createdAt'     => $s->created_at?->toISOString(),
                'saleId'        => null,
                'items'         => [],
            ]);

        // Resumen por forma de pago
        $resumen = [
            'efectivo'      => (float) $ventas->where('forma_de_pago', 'efectivo')->sum('pago'),
            'transferencia' => (float) $ventas->where('forma_de_pago', 'transferencia')->sum('pago'),
            'cobros_deuda'  => (float) Ingreso::where('id_local', $local->id)->whereNotNull('idpersona')->whereDate('created_at', $date)->sum('monto'),
            'egresos'       => (float) Salida::where('id_local', $local->id)->whereDate('created_at', $date)->sum('monto'),
        ];

        $all = $ventasEntries->concat($ingresos)->concat($salidas)
            ->sortByDesc('createdAt')
            ->values();

        return response()->json(['cashflow' => $all, 'resumen' => $resumen]);
    }

    /**
     * POST /api/caja
     * Registrar movimiento manual de caja (ingreso o egreso)
     */
    public function store(Request $request)
    {
        $local = $this->getLocal();
        if (!$local) {
            return response()->json(['message' => 'No tenés un local asignado.'], 403);
        }

        $request->validate([
            'type'        => 'required|in:ingreso,egreso',
            'amount'      => 'required|numeric|min:0.01',
            'description' => 'required|string|max:255',
        ]);

        if ($request->type === 'ingreso') {
            $entry = Ingreso::create([
                'idpersona'   => null,
                'monto'       => $request->amount,
                'descripcion' => $request->description,
                'saldo'       => $request->amount,
                'estado'      => 'activo',
                'id_local'    => $local->id,
            ]);
            $id = 'ING-' . $entry->id_ingreso;
        } else {
            $entry = Salida::create([
                'idpersona'   => null,
                'tipo_salida' => 'gasto',
                'monto'       => $request->amount,
                'descripcion' => $request->description,
                'saldo'       => $request->amount,
                'estado'      => 'activo',
                'id_local'    => $local->id,
            ]);
            $id = 'SAL-' . $entry->idsalida;
        }

        return response()->json([
            'message'  => 'Movimiento registrado',
            'cashflow' => [
                'id'          => $id,
                'type'        => $request->type,
                'amount'      => (float) $request->amount,
                'description' => $request->description,
                'createdAt'   => $entry->created_at->toISOString(),
                'saleId'      => null,
            ],
        ], 201);
    }
}
