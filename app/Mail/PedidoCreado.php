<?php

namespace App\Mail;

use App\Models\Pedido;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PedidoCreado extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $pedido;

    public function __construct(Pedido $pedido)
    {
        $this->pedido = $pedido;
    }

    public function build()
    {
        return $this->subject("Nuevo Pedido #{$this->pedido->id}")
            ->view('emails.pedido_creado')
            ->with([
                'pedido' => $this->pedido,
                'cliente' => $this->pedido->nombre_cliente,
                'total' => $this->pedido->total,
            ]);
    }
}
