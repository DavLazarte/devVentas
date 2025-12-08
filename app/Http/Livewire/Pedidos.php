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
    public $estado_reserva;
    public $isOpen = false;
    public $isDetailOpen = false;
    public $detalles = [];
    public $items = [];
    public $subtotal = 0;
    public $envio = 0;
    public $descuento = 0;
    public $total = 0;
    public $tipo_pedido = 'producto'; // producto, servicio, mixto
    public $fecha_servicio = null;
    public $hora_inicio = null;
    public $tipoPedido;

    protected $listeners = [
        'editarPedido' => 'editar',
        'verDetallePedido' => 'verDetalle',
        'eliminarPedido' => 'eliminar'
    ];
    public $reservas = [];
    public $vista = 'lista';

    public function mount()
    {
        $this->tipoPedido = auth()->user()->local->tipo;

        // The calendar will only be shown for 'servicio' type
        if ($this->tipoPedido === 'servicio') {
            $this->cargarReservas();
        }
    }

    public function updatedVista()
    {
        // Cuando cambie la vista a calendario, recargar las reservas
        if ($this->vista === 'calendario' && $this->tipoPedido === 'servicio') {
            $this->cargarReservas();
            $this->emit('refreshCalendar', $this->reservas);
            // Forzar reinicialización del calendario
            $this->emit('initCalendar');
        }
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

        // Asignar el estado correcto según el tipo de pedido
        if ($pedido->tipo_pedido === 'servicio') {
            $this->estado = $pedido->estado_reserva;
            $this->estado_reserva = $pedido->estado_reserva;
        } else {
            $this->estado = $pedido->estado;
            $this->estado_reserva = $pedido->estado_reserva;
        }

        // Convertir detalles a formato de items con información específica de servicios
        $this->items = $pedido->detalles->map(function ($detalle) {
            $producto = $detalle->producto; // sea Articulo o Servicio
            $variante = $detalle->variantesArticulos; // sea Articulo o Servicio
            $item = [
                'id' => $detalle->idarticulo ?? $detalle->idservicio,
                'name' => $producto->nombre ?? 'Producto',
                'price' => $detalle->precio_unitario,
                'quantity' => $detalle->cantidad, // Solo para productos por unidad
                'variant' => $variante?->descripcion_variante,
                'image' => $producto->imagen_url ?? null,
                'shop' => $pedido->local->nombre ?? 'Tienda',
                'type' => $detalle->idarticulo ? 'articulo' : 'servicio',
                // Campos para peso/volumen
                'cantidad_decimal' => $detalle->cantidad_decimal,
                'unidad_medida' => $detalle->unidad_medida_pedido,
            ];

            // Agregar información específica de servicios
            if ($detalle->idservicio) {
                $item['fecha_reserva'] = $detalle->fecha_reserva;
                $item['hora_reserva'] = $detalle->hora_reserva;
                $item['duracion_servicio'] = $detalle->duracion_servicio;
                $item['empleado_nombre'] = $detalle->empleado ? $detalle->empleado->nombre : null;
                $item['tipo_reserva'] = $producto->tipo_reserva ?? null;
            }

            return $item;
        })->toArray();


        $this->subtotal = $pedido->subtotal;
        $this->envio = $pedido->envio;
        $this->descuento = $pedido->descuento;
        $this->total = $pedido->total;
        $this->fecha_servicio = $pedido->fecha_servicio;
        $this->hora_inicio = $pedido->hora_inicio;

        // Determinar el tipo de pedido
        $this->tipo_pedido = $pedido->tipo_pedido ?? $this->determineOrderType();

        $this->isOpen = true;
    }
    public function cargarReservas()
    {
        $pedidos = Pedido::where('tipo_pedido', 'servicio')
            ->whereNotNull('fecha_servicio')
            ->get();

        $this->reservas = $pedidos->map(function ($pedido) {
            // Determinar el color según el estado_reserva
            $color = '#ffc107'; // Amarillo por defecto (pendiente)
            switch ($pedido->estado_reserva) {
                case 'confirmada':
                    $color = '#28a745'; // Verde
                    break;
                case 'completada':
                    $color = '#17a2b8'; // Azul claro
                    break;
                case 'cancelada':
                    $color = '#dc3545'; // Rojo
                    break;
                case 'pendiente':
                default:
                    $color = '#ffc107'; // Amarillo
                    break;
            }

            return [
                'id'    => $pedido->id,
                'title' => 'Reserva #' . $pedido->id . ' - ' . $pedido->nombre_cliente,
                'start' => $pedido->fecha_servicio . ' ' . $pedido->hora_inicio,
                'end'   => $pedido->fecha_servicio . ' ' . $pedido->hora_fin,
                'color' => $color,
            ];
        })->toArray();
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

        // Asignar el estado correcto según el tipo de pedido
        if ($pedido->tipo_pedido === 'servicio') {
            $this->estado = $pedido->estado_reserva;
            $this->estado_reserva = $pedido->estado_reserva;
        } else {
            $this->estado = $pedido->estado;
            $this->estado_reserva = $pedido->estado_reserva;
        }
        $this->detalles = $pedido->detalles;
        $this->subtotal = $pedido->subtotal;
        $this->envio = $pedido->envio;
        $this->descuento = $pedido->descuento;
        $this->total = $pedido->total;
        $this->fecha_servicio = $pedido->fecha_servicio;
        $this->hora_inicio = $pedido->hora_inicio;

        // Determinar el tipo de pedido
        $this->tipo_pedido = $pedido->tipo_pedido ?? $this->determineOrderType();

        $this->isDetailOpen = true;
    }

    public function eliminar($id)
    {
        try {
            $pedido = Pedido::findOrFail($id);
            $tipoPedido = $pedido->tipo_pedido; // Guardar el tipo antes de eliminar

            $pedido->detalles()->delete(); // Eliminar detalles primero
            $pedido->delete();
            session()->flash('message', 'Pedido eliminado exitosamente.');
            $this->emit('refreshDatatablePedidos');

            // Si era un servicio, refrescar también el calendario
            if ($tipoPedido === 'servicio') {
                $this->cargarReservas();
                $this->emit('refreshCalendar', $this->reservas);
            }
        } catch (\Exception $e) {
            Log::error("Error al eliminar el pedido: " . $e->getMessage());
            session()->flash('error', 'Error al eliminar el pedido: ' . $e->getMessage());
        }
    }

    public function guardar()
    {
        try {

            $pedido = Pedido::find($this->pedido_id);
            // Decidir qué campo actualizar según tipo_pedido
            if ($pedido->tipo_pedido === 'producto') {
                $pedido->update([
                    'estado' => $this->estado,
                ]);
            } elseif ($pedido->tipo_pedido === 'servicio') {
                $pedido->update([
                    'estado_reserva' => $this->estado,
                ]);
            }

            session()->flash('message', 'Estado del pedido actualizado exitosamente.');
            $this->emit('refreshDatatablePedidos');

            // Si es un servicio, refrescar también el calendario
            if ($pedido->tipo_pedido === 'servicio') {
                $this->cargarReservas();
                $this->emit('refreshCalendar', $this->reservas);
            }

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

    private function determineOrderType()
    {
        $tieneArticulos = collect($this->items)->contains('type', 'articulo');
        $tieneServicios = collect($this->items)->contains('type', 'servicio');

        if ($tieneArticulos && $tieneServicios) {
            return 'mixto';
        } elseif ($tieneServicios) {
            return 'servicio';
        } else {
            return 'producto';
        }
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
        $this->tipo_pedido = 'producto';
        $this->fecha_servicio = null;
        $this->hora_inicio = null;
    }

    public function render()
    {
        return view('livewire.pedidos');
    }
}
