<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ingreso;
use App\Models\Local;
use App\Models\Persona;
use App\Models\Venta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ClienteController extends Controller
{
    private function getLocal(): ?Local
    {
        $user = Auth::user();
        if (!$user) return null;
        return Local::where('id_user', $user->id)->first();
    }

    /**
     * GET /api/clientes-pos
     * Lista clientes del local (tipo_persona = clientes)
     */
    public function index(Request $request)
    {
        $local = $this->getLocal();
        if (!$local) {
            return response()->json(['message' => 'No tenés un local asignado.'], 403);
        }

        $type = $request->input('type', 'cliente');
        $query = Persona::where('id_local', $local->id)
            ->where('tipo_persona', $type);

        if ($request->has('search') && $request->search) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('nombre', 'like', "%{$s}%")
                  ->orWhere('telefono', 'like', "%{$s}%")
                  ->orWhere('mail', 'like', "%{$s}%");
            });
        }

        $perPage = $request->input('per_page', 10);
        $paginator = $query->orderBy('nombre')->paginate($perPage);

        $clientes = collect($paginator->items())->map(fn($c) => $this->formatCliente($c));

        return response()->json([
            'clients' => $clientes,
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'total' => $paginator->total(),
            ]
        ]);
    }

    /**
     * POST /api/clientes-pos
     */
    public function store(Request $request)
    {
        $local = $this->getLocal();
        if (!$local) {
            return response()->json(['message' => 'No tenés un local asignado.'], 403);
        }

        $request->validate([
            'name'  => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'email' => 'nullable|email',
        ]);

        $cliente = Persona::create([
            'tipo_persona' => $request->type ?? 'cliente',
            'nombre'       => $request->name,
            'telefono'     => $request->phone,
            'mail'         => $request->email ?? '',
            'estado'       => 'Activo',
            'id_local'     => $local->id,
        ]);

        return response()->json([
            'message' => 'Cliente creado',
            'client'  => $this->formatCliente($cliente),
        ], 201);
    }

    /**
     * PUT /api/clientes-pos/{id}
     */
    public function update(Request $request, $id)
    {
        $local   = $this->getLocal();
        $cliente = Persona::where('id_local', $local?->id)->findOrFail($id);

        $request->validate([
            'name'  => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'email' => 'nullable|email',
        ]);

        $cliente->update([
            'nombre'   => $request->name,
            'telefono' => $request->phone,
            'mail'     => $request->email,
        ]);

        return response()->json([
            'message' => 'Cliente actualizado',
            'client'  => $this->formatCliente($cliente->refresh()),
        ]);
    }

    /**
     * POST /api/clientes-pos/{id}/pago
     * Registra un pago distribuido entre las ventas pendientes del cliente
     */
    public function registrarPago(Request $request, $id)
    {
        $local   = $this->getLocal();
        $cliente = Persona::where('id_local', $local?->id)->findOrFail($id);

        $request->validate([
            'monto'       => 'required|numeric|min:0.01',
            'descripcion' => 'nullable|string|max:255',
        ]);

        // Ventas con saldo pendiente, las más viejas primero
        $ventasPendientes = Venta::where('idcliente', $cliente->idpersona)
            ->where('saldo', '>', 0)
            ->orderBy('created_at', 'asc')
            ->get();

        if ($ventasPendientes->isEmpty()) {
            return response()->json(['message' => 'No hay deudas pendientes para este cliente'], 422);
        }

        DB::beginTransaction();
        try {
            $montoRestante   = (float) $request->monto;
            $ventasAfectadas = [];

            foreach ($ventasPendientes as $venta) {
                if ($montoRestante <= 0) break;

                $aAplicar         = min($montoRestante, (float) $venta->saldo);
                $venta->saldo     = round($venta->saldo - $aAplicar, 2);
                $venta->pago      = round($venta->pago + $aAplicar, 2);
                $venta->save();
                $montoRestante   -= $aAplicar;

                $ventasAfectadas[] = [
                    'ventaId'       => $venta->id,
                    'aplicado'      => $aAplicar,
                    'saldoRestante' => $venta->saldo,
                ];
            }

            // Registrar el ingreso (pago recibido)
            Ingreso::create([
                'idpersona'   => $cliente->idpersona,
                'monto'       => $request->monto,
                'descripcion' => $request->descripcion ?? 'Pago de deuda',
                'saldo'       => $request->monto,
                'estado'      => 'activo',
                'id_local'    => $local->id,
            ]);

            DB::commit();

            return response()->json([
                'message'         => 'Pago registrado exitosamente',
                'client'          => $this->formatCliente($cliente->refresh()),
                'ventasAfectadas' => $ventasAfectadas,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/clientes-pos/{id}/transacciones
     * Registra una deuda manual en la cuenta corriente del cliente
     */
    public function createTransaction(Request $request, $id)
    {
        $local   = $this->getLocal();
        $cliente = Persona::where('id_local', $local?->id)->findOrFail($id);

        $request->validate([
            'amount'      => 'required|numeric|min:0.01',
            'description' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Creamos una Venta para representar esta deuda manual
            $venta = Venta::create([
                'idcliente'     => $cliente->idpersona,
                'tipo_venta'    => 'mostrador',
                'total_venta'   => $request->amount,
                'pago'          => 0,
                'saldo'         => $request->amount,
                'forma_de_pago' => 'cuenta_corriente',
                'estado'        => 'Activo',
                'id_local'      => $local->id,
            ]);

            // Creamos un detalle ficticio con el monto total para que se muestre en el historial
            \App\Models\DetalleVenta::create([
                'idventa'          => $venta->id,
                'cantidad'         => 1,
                'precio_venta'     => $request->amount,
                'estado'           => 'Activo',
                // Dejamos idarticulo null o sin definir si la bd lo permite
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Deuda manual registrada',
                'client'  => $this->formatCliente($cliente->refresh()),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error', 'error' => $e->getMessage()], 500);
        }
    }

    private function formatCliente(Persona $c): array
    {
        $user = Auth::user();
        $isFinanciera = str_starts_with($user->role->name ?? '', 'financiera');

        if ($isFinanciera) {
            $totalDeuda = \App\Models\Credito::where('idpersona', $c->idpersona)
                ->where('saldo_pendiente', '>', 0)
                ->sum('saldo_pendiente');

            $balance = -((float) $totalDeuda);

            $creditos = \App\Models\Credito::with(['plan'])
                ->where('idpersona', $c->idpersona)
                ->orderByDesc('created_at')
                ->get()
                ->map(fn($cr) => [
                    'id' => $cr->id,
                    'monto_aprobado' => (float) $cr->monto_aprobado,
                    'total_a_pagar' => (float) $cr->total_a_pagar,
                    'saldo_pendiente' => (float) $cr->saldo_pendiente,
                    'estado' => $cr->estado,
                    'fecha_otorgamiento' => $cr->fecha_otorgamiento?->toDateString(),
                    'plan_nombre' => $cr->plan ? $cr->plan->nombre : '',
                ]);

            $pagos = Ingreso::where('idpersona', $c->idpersona)
                ->orderByDesc('created_at')
                ->take(20)
                ->get()
                ->map(fn($i) => [
                    'id'          => (string) $i->id_ingreso,
                    'monto'       => (float) $i->monto,
                    'descripcion' => $i->descripcion,
                    'createdAt'   => $i->created_at?->toISOString(),
                ]);

            return [
                'id'           => (string) $c->idpersona,
                'name'         => $c->nombre,
                'phone'        => $c->telefono ?? '',
                'balance'      => (float) $balance,
                'ventas'       => [],
                'creditos'     => $creditos,
                'pagos'        => $pagos,
                'transactions' => [],
            ];
        }

        // Deuda total: suma de saldos pendientes en ventas a cuenta
        $totalDeuda = Venta::where('idcliente', $c->idpersona)
            ->where('saldo', '>', 0)
            ->sum('saldo');

        // Balance: siempre negativo indicando lo que debe. 
        $balance = -((float) $totalDeuda);

        // Todas las ventas del cliente (historial completo)
        $ventas = Venta::with(['detalles.producto', 'detalles.variantesArticulos'])
            ->where('idcliente', $c->idpersona)
            ->orderByDesc('created_at')
            ->take(30)
            ->get()
            ->map(fn($v) => [
                'id'          => (string) $v->id,
                'total'       => (float) $v->total_venta,
                'pago'        => (float) $v->pago,
                'saldo'       => (float) $v->saldo,
                'formaDePago' => $v->forma_de_pago,
                'createdAt'   => $v->created_at?->toISOString(),
                'items'       => $v->detalles->map(function($d) {
                    $variantName = $d->descripcion_variante ?? ($d->variantesArticulos?->descripcion_variante ?? '');
                    return [
                        'productName' => ($d->producto?->nombre ?? 'Producto eliminado') . ($variantName ? ' - ' . $variantName : ''),
                        'quantity'    => $d->cantidad_decimal ?? $d->cantidad,
                        'price'       => (float) $d->precio_venta,
                    ];
                })->values()->toArray(),
            ]);

        // Pagos registrados (ingresos)
        $pagos = Ingreso::where('idpersona', $c->idpersona)
            ->orderByDesc('created_at')
            ->take(20)
            ->get()
            ->map(fn($i) => [
                'id'          => (string) $i->id_ingreso,
                'monto'       => (float) $i->monto,
                'descripcion' => $i->descripcion,
                'createdAt'   => $i->created_at?->toISOString(),
            ]);

        return [
            'id'           => (string) $c->idpersona,
            'name'         => $c->nombre,
            'phone'        => $c->telefono ?? '',
            'balance'      => (float) $balance,
            'ventas'       => $ventas,
            'pagos'        => $pagos,
            // legacy: transactions vacío para no romper nada
            'transactions' => [],
        ];
    }
}
