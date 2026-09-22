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

        // ── Cuentas corrientes: clientes con deuda pendiente ──────────
        if ($request->boolean('with_debt')) {
            $deudores = Persona::where('id_local', $local->id)
                ->where('tipo_persona', 'cliente')
                ->when($request->search, fn($q) =>
                    $q->where(fn($q2) =>
                        $q2->where('nombre', 'like', "%{$request->search}%")
                           ->orWhere('telefono', 'like', "%{$request->search}%")
                    )
                )
                ->get()
                ->map(function ($c) {
                    $saldoVentas = (float) Venta::where('idcliente', $c->idpersona)
                        ->where('saldo', '>', 0)
                        ->sum('saldo');
                    $saldoServicios = (float) Ingreso::where('idpersona', $c->idpersona)
                        ->where('tipo_pago', 'cuenta_corriente')
                        ->where('saldo', '>', 0)
                        ->sum('saldo');
                    $c->_deuda_total = $saldoVentas + $saldoServicios;
                    return $c;
                })
                ->filter(fn($c) => $c->_deuda_total > 0)
                ->sortByDesc('_deuda_total')
                ->values();

            $clients = $deudores->map(fn($c) => $this->formatCliente($c));

            return response()->json([
                'clients'    => $clients,
                'pagination' => [
                    'current_page' => 1,
                    'last_page'    => 1,
                    'total'        => $clients->count(),
                ],
            ]);
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
            'phone' => 'nullable|string|max:50',
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
     * DELETE /api/clientes-pos/{id}
     */
    public function destroy($id)
    {
        $local   = $this->getLocal();
        $cliente = Persona::where('id_local', $local?->id)->findOrFail($id);

        $tieneDeuda = Venta::where('idcliente', $cliente->idpersona)
            ->where('saldo', '>', 0)
            ->exists();

        if ($tieneDeuda) {
            return response()->json([
                'message' => 'No se puede eliminar porque tiene una deuda o saldo pendiente registrado.',
            ], 422);
        }

        $cliente->delete();

        return response()->json([
            'message' => 'Persona eliminada exitosamente',
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

        // Ventas y Servicios a cuenta con saldo pendiente
        $ventasPendientes = Venta::where('idcliente', $cliente->idpersona)
            ->where('saldo', '>', 0)
            ->get();

        $serviciosPendientes = Ingreso::where('idpersona', $cliente->idpersona)
            ->where('tipo_pago', 'cuenta_corriente')
            ->where('saldo', '>', 0)
            ->get();

        if ($ventasPendientes->isEmpty() && $serviciosPendientes->isEmpty()) {
            return response()->json(['message' => 'No hay deudas pendientes para este cliente'], 422);
        }

        DB::beginTransaction();
        try {
            $montoRestante  = (float) $request->monto;
            $itemsAfectados = [];

            // Unir deudas ordenadas por fecha más antigua primero
            $deudas = collect();
            foreach ($ventasPendientes as $v) {
                $deudas->push((object)[
                    'tipo'       => 'venta',
                    'model'      => $v,
                    'created_at' => $v->created_at,
                    'saldo'      => (float) $v->saldo,
                ]);
            }
            foreach ($serviciosPendientes as $s) {
                $deudas->push((object)[
                    'tipo'       => 'servicio',
                    'model'      => $s,
                    'created_at' => $s->created_at,
                    'saldo'      => (float) $s->saldo,
                ]);
            }
            $deudas = $deudas->sortBy('created_at')->values();

            foreach ($deudas as $item) {
                if ($montoRestante <= 0) break;

                $aAplicar = min($montoRestante, $item->saldo);
                if ($item->tipo === 'venta') {
                    $venta = $item->model;
                    $venta->saldo = round($venta->saldo - $aAplicar, 2);
                    $venta->pago  = round($venta->pago + $aAplicar, 2);
                    $venta->save();
                    $itemsAfectados[] = [
                        'tipo'          => 'venta',
                        'id'            => $venta->id,
                        'aplicado'      => $aAplicar,
                        'saldoRestante' => $venta->saldo,
                    ];
                } else {
                    $servicio = $item->model;
                    $servicio->saldo = round($servicio->saldo - $aAplicar, 2);
                    $servicio->save();
                    $itemsAfectados[] = [
                        'tipo'          => 'servicio',
                        'id'            => $servicio->id_ingreso,
                        'aplicado'      => $aAplicar,
                        'saldoRestante' => $servicio->saldo,
                    ];
                }
                $montoRestante -= $aAplicar;
            }

            // Registrar el ingreso (pago recibido en efectivo/transferencia)
            Ingreso::create([
                'idpersona'   => $cliente->idpersona,
                'monto'       => $request->monto,
                'tipo_pago'   => $request->input('paymentMethod') ?? $request->input('tipo_pago') ?? 'efectivo',
                'descripcion' => $request->descripcion ?? 'Pago de deuda',
                'saldo'       => 0,
                'estado'      => 'activo',
                'id_local'    => $local->id,
            ]);

            DB::commit();

            return response()->json([
                'message'         => 'Pago registrado exitosamente',
                'client'          => $this->formatCliente($cliente->refresh()),
                'ventasAfectadas' => $itemsAfectados,
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
            try {
                $articuloId = \App\Models\Articulo::where('id_local', $local->id)->value('idarticulo');
                $detalleData = [
                    'idventa'              => $venta->id,
                    'cantidad'             => 1,
                    'precio_venta'         => $request->amount,
                    'estado'               => 'Activo',
                    'descripcion_variante' => $request->description ?? 'Servicio / Deuda a cuenta',
                ];
                if ($articuloId) {
                    $detalleData['idarticulo'] = $articuloId;
                }
                \App\Models\DetalleVenta::create($detalleData);
            } catch (\Exception $eDet) {
                \Illuminate\Support\Facades\Log::warning('DetalleVenta manual omitido: ' . $eDet->getMessage());
            }

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

    /**
     * POST /api/clientes-pos/{id}/pagar-comision
     * Registra el pago de comisión / liquidación a un empleado y crea la Salida en Caja
     */
    public function pagarComision(Request $request, $id)
    {
        $local = $this->getLocal();
        $empleado = Persona::where('id_local', $local?->id)
            ->where('tipo_persona', 'empleado')
            ->findOrFail($id);

        $request->validate([
            'monto'       => 'required|numeric|min:0.01',
            'porcentaje'  => 'nullable|numeric|min:0|max:100',
            'tipo_pago'   => 'nullable|string',
            'descripcion' => 'nullable|string|max:255',
        ]);

        $monto = (float) $request->monto;
        $descripcion = $request->descripcion;
        if (empty($descripcion)) {
            $porcentajeStr = $request->porcentaje ? " ({$request->porcentaje}%)" : "";
            $descripcion = "Liquidación comisión{$porcentajeStr} - {$empleado->nombre}";
        }

        DB::beginTransaction();
        try {
            $salida = \App\Models\Salida::create([
                'idpersona'   => $empleado->idpersona,
                'tipo_salida' => 'comision',
                'monto'       => $monto,
                'descripcion' => $descripcion,
                'saldo'       => 0,
                'estado'      => 'activo',
                'id_local'    => $local->id,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Comisión liquidada y salida registrada en caja exitosamente',
                'client'  => $this->formatCliente($empleado->refresh()),
                'salida'  => $salida,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al registrar salida', 'error' => $e->getMessage()], 500);
        }
    }

    private function formatCliente(Persona $c): array
    {
        $user = Auth::user();
        $isFinanciera = str_starts_with($user->role->name ?? '', 'financiera');

        // ── Si es empleado/personal: mostrar actividad profesional, cortes y servicios ──
        if ($c->tipo_persona === 'empleado') {
            $serviciosAsignados = $c->servicios()
                ->get(['servicios.idservicio', 'servicios.nombre', 'servicios.precio', 'servicios.duracion']);

            $detallesHoy = \App\Models\DetallePedido::with(['pedido', 'servicio'])
                ->where('id_empleado', $c->idpersona)
                ->whereDate('created_at', \Carbon\Carbon::today())
                ->get();

            $cortesHoy = $detallesHoy->count();
            $recaudadoHoy = (float) $detallesHoy->sum('subtotal');

            $historialServicios = \App\Models\DetallePedido::with(['pedido', 'servicio'])
                ->where('id_empleado', $c->idpersona)
                ->orderByDesc('created_at')
                ->take(30)
                ->get()
                ->map(fn($d) => [
                    'id'             => (string) $d->id,
                    'servicioNombre' => $d->servicio?->nombre ?? 'Servicio',
                    'clienteNombre'  => $d->pedido?->nombre_cliente ?? 'Cliente mostrador',
                    'monto'          => (float) ($d->subtotal ?? $d->precio_unitario),
                    'estado'         => $d->pedido?->estado_atencion ?? $d->pedido?->estado ?? 'completado',
                    'createdAt'      => $d->created_at?->toISOString(),
                ]);

            $liquidaciones = \App\Models\Salida::where('idpersona', $c->idpersona)
                ->orderByDesc('created_at')
                ->take(30)
                ->get()
                ->map(fn($s) => [
                    'id'          => (string) $s->idsalida,
                    'monto'       => (float) $s->monto,
                    'descripcion' => $s->descripcion,
                    'createdAt'   => $s->created_at?->toISOString(),
                ]);

            return [
                'id'                  => (string) $c->idpersona,
                'name'                => $c->nombre,
                'phone'               => $c->telefono ?? '',
                'type'                => 'empleado',
                'balance'             => 0,
                'cortes_hoy'          => $cortesHoy,
                'recaudado_hoy'       => $recaudadoHoy,
                'servicios_asignados' => $serviciosAsignados,
                'historial_servicios' => $historialServicios,
                'ventas'              => [],
                'pagos'               => $liquidaciones,
                'transactions'        => [],
            ];
        }

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

        // Deuda total: suma de saldos pendientes en ventas a cuenta + servicios a cuenta
        $deudaVentas = (float) Venta::where('idcliente', $c->idpersona)
            ->where('saldo', '>', 0)
            ->sum('saldo');
        $deudaServicios = (float) Ingreso::where('idpersona', $c->idpersona)
            ->where('tipo_pago', 'cuenta_corriente')
            ->where('saldo', '>', 0)
            ->sum('saldo');
        $totalDeuda = $deudaVentas + $deudaServicios;

        // Balance: siempre negativo indicando lo que debe. 
        $balance = -((float) $totalDeuda);

        // Todas las ventas del cliente (historial de ventas de productos)
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
                    $nombre = $d->producto 
                        ? ($d->producto->nombre . ($variantName ? ' - ' . $variantName : '')) 
                        : ($variantName ?: 'Servicio / Venta');
                    return [
                        'productName' => $nombre,
                        'quantity'    => $d->cantidad_decimal ?? $d->cantidad ?? 1,
                        'price'       => (float) $d->precio_venta,
                    ];
                })->values()->toArray(),
            ]);

        // Servicios a cuenta (deudas de turnos/servicios)
        $serviciosCuenta = Ingreso::where('idpersona', $c->idpersona)
            ->where('tipo_pago', 'cuenta_corriente')
            ->orderByDesc('created_at')
            ->take(30)
            ->get()
            ->map(fn($ing) => [
                'id'          => 'SRV-' . $ing->id_ingreso,
                'total'       => (float) $ing->monto,
                'pago'        => (float) max(0, round($ing->monto - $ing->saldo, 2)),
                'saldo'       => (float) $ing->saldo,
                'formaDePago' => 'cuenta_corriente',
                'createdAt'   => $ing->created_at?->toISOString(),
                'items'       => [
                    [
                        'productName' => $ing->descripcion ?: 'Servicio a cuenta',
                        'quantity'    => 1,
                        'price'       => (float) $ing->monto,
                    ]
                ],
            ]);

        $todasLasVentas = $ventas->concat($serviciosCuenta)->sortByDesc('createdAt')->values();

        // Pagos recibidos registrados (ingresos cobrados, excluyendo deudas pendientes 'cuenta_corriente')
        $pagos = Ingreso::where('idpersona', $c->idpersona)
            ->where(function($q) {
                $q->where('tipo_pago', '!=', 'cuenta_corriente')
                  ->orWhereNull('tipo_pago');
            })
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
            'type'         => $c->tipo_persona ?? 'cliente',
            'balance'      => (float) $balance,
            'ventas'       => $todasLasVentas,
            'pagos'        => $pagos,
            // legacy: transactions vacío para no romper nada
            'transactions' => [],
        ];
    }
}
