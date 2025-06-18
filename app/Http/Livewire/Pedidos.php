<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Pedido;
use App\Models\DetallePedido;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class Pedidos extends Component
{
    public $pedido_id;
    public $nombre_cliente;
    public $email;
    public $telefono;
    public $direccion;
    public $ciudad;
    public $codigo_postal;
    public $notas_entrega;
    public $metodo_pago;
    public $estado;
    public $isOpen = false;
    public $isDetailOpen = false;
    public $detalles = [];
    public $items = [];
    public $subtotal = 0;
    public $envio = 0;
    public $descuento = 0;
    public $total = 0;

    protected $listeners = [
        'editarPedido' => 'editar',
        'verDetallePedido' => 'verDetalle',
        'eliminarPedido' => 'eliminar'
    ];

    public function mount()
    {
        // Inicialización si es necesaria
    }

    public function editar($id)
    {
        $pedido = Pedido::with('detalles')->findOrFail($id);
        $this->pedido_id = $id;
        $this->nombre_cliente = $pedido->nombre_cliente;
        $this->email = $pedido->email;
        $this->telefono = $pedido->telefono;
        $this->direccion = $pedido->direccion;
        $this->ciudad = $pedido->ciudad;
        $this->codigo_postal = $pedido->codigo_postal;
        $this->notas_entrega = $pedido->notas_entrega;
        $this->metodo_pago = $pedido->metodo_pago;
        $this->estado = $pedido->estado;

        // Convertir detalles a formato de items
        $this->items = $pedido->detalles->map(function($detalle) {
            $producto = $detalle->producto; // sea Articulo o Servicio
            return [
                'id' => $detalle->idarticulo ?? $detalle->idservicio,
                'name' => $producto->nombre ?? 'Producto',
                'price' => $detalle->precio_unitario,
                'quantity' => $detalle->cantidad,
                'variant' => $detalle->variante,
                'image' => $producto->imagen_url ?? asset('images/default-product.jpg'),
                'shop' => $pedido->local->nombre ?? 'Tienda',
                'type' => $detalle->idarticulo ? 'articulo' : 'servicio'
            ];
        })->toArray();


        $this->subtotal = $pedido->subtotal;
        $this->envio = $pedido->envio;
        $this->descuento = $pedido->descuento;
        $this->total = $pedido->total;

        $this->isOpen = true;
    }

    public function verDetalle($id)
    {
        $pedido = Pedido::with('detalles')->findOrFail($id);
        $this->pedido_id = $id;
        $this->nombre_cliente = $pedido->nombre_cliente;
        $this->email = $pedido->email;
        $this->telefono = $pedido->telefono;
        $this->direccion = $pedido->direccion;
        $this->ciudad = $pedido->ciudad;
        $this->codigo_postal = $pedido->codigo_postal;
        $this->notas_entrega = $pedido->notas_entrega;
        $this->metodo_pago = $pedido->metodo_pago;
        $this->estado = $pedido->estado;
        $this->detalles = $pedido->detalles;
        $this->subtotal = $pedido->subtotal;
        $this->envio = $pedido->envio;
        $this->descuento = $pedido->descuento;
        $this->total = $pedido->total;
        $this->isDetailOpen = true;
    }

    public function eliminar($id)
    {
        try {
            $pedido = Pedido::findOrFail($id);
            $pedido->detalles()->delete(); // Eliminar detalles primero
            $pedido->delete();
            session()->flash('message', 'Pedido eliminado exitosamente.');
            $this->emit('refreshDatatablePedidos');
        } catch (\Exception $e) {
            Log::error("Error al eliminar el pedido: " . $e->getMessage());
            session()->flash('error', 'Error al eliminar el pedido: ' . $e->getMessage());
        }
    }

    public function guardar()
    {
        try {
            $this->validate([
                'estado' => 'required'
            ]);

            $pedido = Pedido::find($this->pedido_id);
            $pedido->update([
                'estado' => $this->estado
            ]);

            session()->flash('message', 'Estado del pedido actualizado exitosamente.');
            $this->emit('refreshDatatablePedidos');
            $this->closeModal();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error al actualizar el estado del pedido: " . $e->getMessage());
            session()->flash('error', 'Error al actualizar el estado del pedido: ' . $e->getMessage());
        }
    }

    public function closeModal()
    {
        $this->isOpen = false;
        $this->isDetailOpen = false;
        $this->resetInputFields();
    }

    private function resetInputFields()
    {
        $this->pedido_id = null;
        $this->nombre_cliente = '';
        $this->email = '';
        $this->telefono = '';
        $this->direccion = '';
        $this->ciudad = '';
        $this->codigo_postal = '';
        $this->notas_entrega = '';
        $this->metodo_pago = '';
        $this->estado = '';
        $this->detalles = [];
        $this->items = [];
        $this->subtotal = 0;
        $this->envio = 0;
        $this->descuento = 0;
        $this->total = 0;
    }

    public function render()
    {
        return view('livewire.pedidos');
    }
}
