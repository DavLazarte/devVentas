<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\Persona;
use App\Models\Ingreso;
use App\Models\Local;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StaffController extends Controller
{
    /**
     * Busca al empleado por token y valida que sea del tipo correcto.
     */
    private function resolveEmpleado(string $token): Persona
    {
        return Persona::with('servicios', 'user')
            ->whereIn('tipo_persona', ['empleado', 'instructor', 'staff'])
            ->where('token_staff', $token)
            ->firstOrFail();
    }

    /**
     * Verifica que un turno pertenezca a uno de los servicios del empleado.
     */
    private function verificarTurnoDeEmpleado(Pedido $turno, Persona $empleado): bool
    {
        $serviciosIds = $empleado->servicios()->pluck('idservicio');
        return $turno->detalles->contains(fn($d) => $serviciosIds->contains($d->idservicio));
    }

    /**
     * GET /api/staff/{token}?fecha=YYYY-MM-DD
     * Panel del empleado: sus turnos del día
     */
    public function show($token, Request $request)
    {
        $empleado = $this->resolveEmpleado($token);

        $fecha = $request->query('fecha', Carbon::now('America/Argentina/Buenos_Aires')->toDateString());

        $local = Local::find($empleado->id_local);

        // Servicios asignados a este empleado
        $serviciosIds = $empleado->servicios()->pluck('servicios.idservicio')->toArray();

        // Turnos del día para los servicios de este empleado (pendientes y en atención)
        $pedidos = Pedido::with('detalles.servicio')
            ->where('tipo_pedido', 'servicio')
            ->where('id_local', $empleado->id_local)
            ->where(function ($q) use ($fecha) {
                $q->whereDate('fecha_servicio', $fecha)
                  ->orWhereDate('created_at', $fecha);
            })
            ->whereNotIn('estado_atencion', ['completado', 'ausente'])
            ->whereHas('detalles', fn($q) => $q->whereIn('idservicio', $serviciosIds))
            ->orderByRaw('-posicion_cola DESC') // nulls al final
            ->orderBy('hora_inicio', 'asc')
            ->orderBy('hora_estimada', 'asc')
            ->get();

        // Historial: todos los cortes atendidos o cancelados hoy para este empleado o sus servicios
        $pedidosHistorial = Pedido::with('detalles.servicio')
            ->where('tipo_pedido', 'servicio')
            ->where('id_local', $empleado->id_local)
            ->where(function ($q) use ($fecha) {
                $q->whereDate('fecha_servicio', $fecha)
                  ->orWhereDate('created_at', $fecha);
            })
            ->whereIn('estado_atencion', ['completado', 'ausente'])
            ->whereHas('detalles', function ($q) use ($empleado, $serviciosIds) {
                $q->where('id_empleado', $empleado->idpersona)
                  ->orWhere(function ($sub) use ($serviciosIds) {
                      $sub->whereNull('id_empleado')
                          ->whereIn('idservicio', $serviciosIds);
                  });
            })
            ->orderBy('updated_at', 'desc')
            ->get();

        $formatearTurno = function ($p) {
            $detalle = $p->detalles->first();
            return [
                'id'              => $p->id,
                'nombre_cliente'  => $p->nombre_cliente,
                'telefono'        => $p->telefono,
                'posicion_cola'   => $p->posicion_cola,
                'slot_hora'       => $p->hora_inicio,
                'hora_estimada'   => $p->hora_estimada,
                'estado_atencion' => $p->estado_atencion,
                'fecha_servicio'  => $p->fecha_servicio,
                'servicio'        => $detalle?->servicio?->nombre ?? 'Servicio',
                'precio'          => (float) ($detalle?->subtotal ?? $detalle?->precio_unitario ?? $detalle?->servicio?->precio ?? 0),
                'tipo_reserva'    => $detalle?->servicio?->tipo_reserva ?? 'cola_virtual',
                'token_publico'   => $p->token_publico,
            ];
        };

        $turnos = $pedidos->map($formatearTurno);
        $historial = $pedidosHistorial->map($formatearTurno);

        $cortesCompletados = $pedidosHistorial->where('estado_atencion', 'completado');
        $cortesHoyCount = $cortesCompletados->count();
        $totalRecaudadoHoy = (float) $cortesCompletados->sum(function ($p) {
            $detalle = $p->detalles->first();
            return (float) ($p->total ?? $detalle?->subtotal ?? $detalle?->precio_unitario ?? $detalle?->servicio?->precio ?? 0);
        });

        return response()->json([
            'empleado' => [
                'id'         => $empleado->idpersona,
                'nombre'     => $empleado->nombre,
                'tipo'       => $empleado->tipo_persona,
                'cortes_hoy' => $cortesHoyCount,
                'total_hoy'  => $totalRecaudadoHoy,
            ],
            'local' => [
                'nombre' => $local?->nombre ?? '',
                'slug'   => $local?->slug ?? '',
            ],
            'fecha'      => $fecha,
            'cortes_hoy' => $cortesHoyCount,
            'total_hoy'  => $totalRecaudadoHoy,
            'turnos'     => $turnos->values(),
            'historial'  => $historial->values(),
        ]);
    }

    /**
     * PATCH /api/staff/{token}/turnos/{id}/llamar
     * El empleado llama a un cliente específico
     */
    public function llamar($token, $id)
    {
        $empleado = $this->resolveEmpleado($token);

        $turno = Pedido::with('detalles.servicio')
            ->where('id_local', $empleado->id_local)
            ->findOrFail($id);

        if (!$this->verificarTurnoDeEmpleado($turno, $empleado)) {
            return response()->json(['success' => false, 'error' => 'Turno no pertenece a tus servicios'], 403);
        }

        DB::beginTransaction();
        try {
            $turno->update(['estado_atencion' => 'siendo_atendido']);

            // Al llamar, asignar el id_empleado en el detalle para contabilizar el corte
            $turno->detalles()->update(['id_empleado' => $empleado->idpersona]);

            // Recalcular horas estimadas de los que siguen (mismo servicio)
            $detalle         = $turno->detalles->first();
            $servicioId      = $detalle?->idservicio;
            $minutosPorTurno = ($detalle?->servicio?->duracion ?? 30) + ($detalle?->servicio?->buffer_tiempo ?? 0);

            $losDemas = Pedido::where('tipo_pedido', 'servicio')
                ->where('id_local', $empleado->id_local)
                ->whereDate('fecha_servicio', $turno->fecha_servicio)
                ->where('estado_atencion', 'en_espera')
                ->where('posicion_cola', '>', $turno->posicion_cola)
                ->whereHas('detalles', fn($q) => $q->where('idservicio', $servicioId))
                ->orderBy('posicion_cola', 'asc')
                ->get();

            $horaBase = Carbon::now();
            foreach ($losDemas as $index => $esperando) {
                $nuevaHora = $horaBase->copy()->addMinutes($minutosPorTurno * ($index + 1));
                $esperando->update(['hora_estimada' => $nuevaHora->format('H:i:s')]);
            }

            DB::commit();
            return response()->json(['success' => true, 'mensaje' => 'Turno llamado exitosamente']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * PATCH /api/staff/{token}/turnos/{id}/completar
     * El empleado completa el turno y registra el cobro en caja
     */
    public function completar($token, $id, Request $request)
    {
        $empleado = $this->resolveEmpleado($token);

        $turno = Pedido::with('detalles.servicio')
            ->where('id_local', $empleado->id_local)
            ->findOrFail($id);

        if (!$this->verificarTurnoDeEmpleado($turno, $empleado)) {
            return response()->json(['success' => false, 'error' => 'Turno no pertenece a tus servicios'], 403);
        }

        $turno->update(['estado_atencion' => 'completado', 'estado' => 'entregado']);

        // Vincular el empleado al detalle (para contar cortes/atenciones)
        $turno->detalles()->update(['id_empleado' => $empleado->idpersona]);

        $monto           = (float) ($request->input('monto') ?? $turno->total ?? 0);
        $metodoPago      = $request->input('metodo_pago');
        $nombreCliente   = trim($turno->nombre_cliente ?? 'Cliente');
        $telefonoCliente = trim($turno->telefono ?? '');
        $servicioNombre  = $turno->detalles->first()?->servicio?->nombre ?? 'Servicio';
        $localId         = $turno->id_local;

        // Buscar o registrar al cliente en Personas
        $clientePersona = null;
        if (!empty($telefonoCliente) && $telefonoCliente !== '0') {
            $clientePersona = Persona::where('id_local', $localId)
                ->where('tipo_persona', 'cliente')
                ->where('telefono', $telefonoCliente)
                ->first();
        }
        if (!$clientePersona && !empty($nombreCliente)) {
            $clientePersona = Persona::where('id_local', $localId)
                ->where('tipo_persona', 'cliente')
                ->where('nombre', $nombreCliente)
                ->first();
        }
        if (!$clientePersona && !empty($nombreCliente)) {
            $clientePersona = Persona::create([
                'tipo_persona' => 'cliente',
                'nombre'       => $nombreCliente,
                'telefono'     => !empty($telefonoCliente) && $telefonoCliente !== '0' ? $telefonoCliente : '',
                'mail'         => '',
                'estado'       => 'Activo',
                'id_local'     => $localId,
            ]);
        }

        // ── Pago mixto
        $montoEfectivo      = (float) $request->input('monto_efectivo', 0);
        $montoTransferencia = (float) $request->input('monto_transferencia', 0);
        $montoCuentaCte     = (float) $request->input('monto_cuenta_corriente', 0);

        if ($montoEfectivo === 0.0 && $montoTransferencia === 0.0 && $montoCuentaCte === 0.0 && $monto > 0 && !empty($metodoPago)) {
            if ($metodoPago === 'cuenta_corriente')  $montoCuentaCte     = $monto;
            elseif ($metodoPago === 'transferencia') $montoTransferencia = $monto;
            else                                     $montoEfectivo      = $monto;
        }

        // ── Saldo a favor: montos
        $montoSaldoFavor = (float) $request->input('monto_saldo_favor', 0);
        $montoUsarSaldo  = (float) $request->input('monto_usar_saldo_favor', 0);

        $notaSaldo = '';
        if ($montoSaldoFavor > 0) {
            $notaSaldo = ' (+$' . number_format($montoSaldoFavor, 0, ',', '.') . ' a favor)';
        } elseif ($montoUsarSaldo > 0) {
            $notaSaldo = ' (usó $' . number_format($montoUsarSaldo, 0, ',', '.') . ' saldo a favor)';
        }

        $prefijo = "Cobro de turno: {$servicioNombre}" . ($clientePersona ? " - {$clientePersona->nombre}" : '') . " (por {$empleado->nombre})" . $notaSaldo;

        if ($montoEfectivo > 0) {
            Ingreso::create(['idpersona'=>$clientePersona?->idpersona,'monto'=>$montoEfectivo,'tipo_pago'=>'efectivo','descripcion'=>$prefijo,'saldo'=>0,'estado'=>'activo','id_local'=>$localId]);
        }
        if ($montoTransferencia > 0) {
            Ingreso::create(['idpersona'=>$clientePersona?->idpersona,'monto'=>$montoTransferencia,'tipo_pago'=>'transferencia','descripcion'=>$prefijo,'saldo'=>0,'estado'=>'activo','id_local'=>$localId]);
        }
        if ($montoCuentaCte > 0) {
            Ingreso::create(['idpersona'=>$clientePersona?->idpersona,'monto'=>$montoCuentaCte,'tipo_pago'=>'cuenta_corriente','descripcion'=>"Servicio a cuenta: {$servicioNombre}" . ($clientePersona ? " - {$clientePersona->nombre}" : '') . " (por {$empleado->nombre})", 'saldo'=>$montoCuentaCte,'estado'=>'activo','id_local'=>$localId]);
        }

        // ── Saldo a favor: guardar vuelto o excedente
        if ($montoSaldoFavor > 0 && $clientePersona) {
            $clientePersona->saldo_favor = round(($clientePersona->saldo_favor ?? 0) + $montoSaldoFavor, 2);
            $clientePersona->save();
        }

        // ── Saldo a favor: descontar si el cliente pagó usando saldo a favor
        if ($montoUsarSaldo > 0 && $clientePersona && ($clientePersona->saldo_favor ?? 0) >= $montoUsarSaldo) {
            $clientePersona->saldo_favor = round(max(0, ($clientePersona->saldo_favor ?? 0) - $montoUsarSaldo), 2);
            $clientePersona->save();
        }

        return response()->json(['success' => true, 'mensaje' => 'Turno completado exitosamente']);
    }

    /**
     * PATCH /api/staff/{token}/turnos/{id}/cancelar
     * El empleado marca al cliente como ausente
     */
    public function cancelar($token, $id)
    {
        $empleado = $this->resolveEmpleado($token);

        $turno = Pedido::with('detalles')
            ->where('id_local', $empleado->id_local)
            ->findOrFail($id);

        if (!$this->verificarTurnoDeEmpleado($turno, $empleado)) {
            return response()->json(['success' => false, 'error' => 'Turno no pertenece a tus servicios'], 403);
        }

        $turno->detalles()->update(['id_empleado' => $empleado->idpersona]);
        $turno->update([
            'estado_atencion' => 'ausente',
            'estado_reserva'  => 'cancelada',
            'estado'          => 'cancelado',
        ]);

        return response()->json(['success' => true]);
    }
}
