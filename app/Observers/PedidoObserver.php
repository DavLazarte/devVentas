<?php

namespace App\Observers;

use App\Models\Pedido;
use App\Mail\PedidoCreado;
use App\Mail\PedidoConfirmacion;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class PedidoObserver
{
    /**
     * Handle the Pedido "created" event.
     *
     * @param  \App\Models\Pedido  $pedido
     * @return void
     */
    public function created(Pedido $pedido)
    {
        // TODO: reactivar cuando se configure SMTP + QUEUE_CONNECTION != sync
        // try {
        //     $emailDueno = $pedido->local->user->email ?? null;
        //     if ($emailDueno) {
        //         Mail::to($emailDueno)->queue(new PedidoCreado($pedido));
        //         Log::info("Email de pedido #{$pedido->id} encolado para {$emailDueno}");
        //     }
        //     $emailCliente = $pedido->email ?? null;
        //     if ($emailCliente) {
        //         Mail::to($emailCliente)->queue(new PedidoConfirmacion($pedido));
        //         Log::info("Email de confirmación de pedido #{$pedido->id} encolado para {$emailCliente}");
        //     }
        // } catch (\Exception $e) {
        //     Log::error("Error al encolar email de pedido: " . $e->getMessage());
        // }
    }

    /**
     * Handle the Pedido "updated" event.
     *
     * @param  \App\Models\Pedido  $pedido
     * @return void
     */
    public function updated(Pedido $pedido)
    {
        //
    }

    /**
     * Handle the Pedido "deleted" event.
     *
     * @param  \App\Models\Pedido  $pedido
     * @return void
     */
    public function deleted(Pedido $pedido)
    {
        //
    }

    /**
     * Handle the Pedido "restored" event.
     *
     * @param  \App\Models\Pedido  $pedido
     * @return void
     */
    public function restored(Pedido $pedido)
    {
        //
    }

    /**
     * Handle the Pedido "force deleted" event.
     *
     * @param  \App\Models\Pedido  $pedido
     * @return void
     */
    public function forceDeleted(Pedido $pedido)
    {
        //
    }
}
