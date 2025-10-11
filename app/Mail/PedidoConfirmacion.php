<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\Pedido;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PedidoConfirmacion extends Mailable
{
    use Queueable, SerializesModels;

    public $pedido;

    public function __construct(Pedido $pedido)
    {
        $this->pedido = $pedido;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject("Tu pedido #{$this->pedido->id} ha sido confirmado")
            ->view('emails.pedido_confirmacion')
            ->with([
                'pedido' => $this->pedido,
                'cliente' => $this->pedido->nombre_cliente,
                'total' => $this->pedido->total,
            ]);
    }
}
