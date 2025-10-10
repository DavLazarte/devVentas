<?php

namespace App\Observers;

use App\Models\Pedido;
use App\Mail\PedidoCreado;
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
        try {
            // Obtener el email del dueño de la tienda
            $emailDueno = $pedido->local->user->email ?? null;

            if ($emailDueno) {
                Mail::to($emailDueno)->send(new PedidoCreado($pedido));

                Log::info("Email de pedido #{$pedido->id} enviado a {$emailDueno}");
            }
        } catch (\Exception $e) {
            Log::error("Error al enviar email de pedido: " . $e->getMessage());
        }
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
