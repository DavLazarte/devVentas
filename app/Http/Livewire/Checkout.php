<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Pedido;
use App\Models\DetallePedido;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class Checkout extends Component
{
    // Propiedades para el formulario
    public $nombre_cliente;
    public $email;
    public $telefono;
    public $direccion;
    public $ciudad;
    public $codigo_postal;
    public $notas_entrega;
    public $metodo_pago = 'efectivo';
    public $crear_cuenta = false;
    public $acepto_terminos = false;

    // Propiedades para los totales
    public $subtotal = 0;
    public $envio = 0;
    public $descuento = 0;
    public $total = 0;

    // Items del carrito
    public $items = [];

    protected $rules = [
        'nombre_cliente' => 'required|min:3',
        'email' => 'required|email',
        'telefono' => 'required',
        'direccion' => 'required',
        'ciudad' => 'required',
        'codigo_postal' => 'required',
        'metodo_pago' => 'required|in:efectivo,tarjeta',
        'acepto_terminos' => 'required|accepted',
    ];

    protected $messages = [
        'nombre_cliente.required' => 'El nombre es obligatorio',
        'nombre_cliente.min' => 'El nombre debe tener al menos 3 caracteres',
        'email.required' => 'El email es obligatorio',
        'email.email' => 'El email debe ser válido',
        'telefono.required' => 'El teléfono es obligatorio',
        'direccion.required' => 'La dirección es obligatoria',
        'ciudad.required' => 'La ciudad es obligatoria',
        'codigo_postal.required' => 'El código postal es obligatorio',
        'metodo_pago.required' => 'Debes seleccionar un método de pago',
        'metodo_pago.in' => 'El método de pago seleccionado no es válido',
        'acepto_terminos.required' => 'Debes aceptar los términos y condiciones',
        'acepto_terminos.accepted' => 'Debes aceptar los términos y condiciones',
    ];

    public function mount()
    {
        // Verificar si hay una reserva directa de servicio
        $directService = session()->get('direct_service_booking');

        if ($directService) {
            // Si hay una reserva directa, usar solo ese servicio
            $this->items = [$directService];
        } else {
            // Tu código existente para cargar items del carrito
            $this->items = session()->get('cart', []);
        }
        // Si no hay items, redirigir o mostrar error
        if (empty($this->items)) {
            session()->flash('error', 'No hay elementos en el carrito');
            return redirect()->route('home'); // o la ruta que prefieras
        }
        // Calcular totales
        $this->calcularTotales();

        // Si el usuario está autenticado, pre-llenar datos
        if (Auth::check()) {
            $user = Auth::user();
            $this->nombre_cliente = $user->name;
            $this->email = $user->email;
            $this->telefono = $user->phone ?? '';
        }
    }

    public function calcularTotales()
    {
        $this->subtotal = collect($this->items)->sum(function ($item) {
            return $item['price'] * $item['quantity'];
        });

        $this->total = $this->subtotal + $this->envio - $this->descuento;
    }



    public function procesarPedido()
    {

        try {
            if (empty($this->items)) {
                session()->flash('error', 'No hay elementos para procesar');
                return;
            }

            $primerItem = reset($this->items);
            $id_local = $primerItem['shop_id'] ?? null;

            if (!$id_local) {
                session()->flash('error', 'Error al procesar el pedido: información de tienda no disponible');
                return;
            }

            // Determinar qué tipos de elementos tenemos
            $tieneServicios = collect($this->items)->contains('type', 'servicio');
            $tieneProductos = collect($this->items)->contains('type', 'articulo');

            // Validaciones condicionales
            $rules = [
                'nombre_cliente' => 'required|min:3',
                'email' => 'required|email',
                'telefono' => 'required',
                'metodo_pago' => 'required',
                'acepto_terminos' => 'required|accepted'
            ];

            $messages = [
                'nombre_cliente.required' => 'El nombre es obligatorio',
                'email.required' => 'El email es obligatorio',
                'email.email' => 'El email debe ser válido',
                'telefono.required' => 'El teléfono es obligatorio',
                'metodo_pago.required' => 'Debes seleccionar un método de pago',
                'acepto_terminos.required' => 'Debes aceptar los términos y condiciones',
                'acepto_terminos.accepted' => 'Debes aceptar los términos y condiciones'
            ];

            // Solo requerir dirección para productos
            if ($tieneProductos) {
                $rules['direccion'] = 'required';
                $rules['ciudad'] = 'required';
                $rules['codigo_postal'] = 'required';

                $messages['direccion.required'] = 'La dirección es obligatoria';
                $messages['ciudad.required'] = 'La ciudad es obligatoria';
                $messages['codigo_postal.required'] = 'El código postal es obligatorio';
            }

            $this->validate($rules, $messages);

            // Determinar el tipo de pedido
            $tipoPedido = $tieneServicios && $tieneProductos ? 'mixto' : ($tieneServicios ? 'servicio' : 'producto');

            // Para servicios, obtener fecha y hora de reserva del primer servicio
            $fechaServicio = null;
            $horaInicio = null;

            if ($tieneServicios) {

                $primerServicio = collect($this->items)->first(function ($item) {
                    return $item['type'] === 'servicio';
                });

                if (isset($primerServicio['fecha_servicio'])) {
                    $fechaServicio = $primerServicio['fecha_servicio'];
                }
                if (isset($primerServicio['hora_inicio'])) {
                    $horaInicio = $primerServicio['hora_inicio'];
                }
                // Calcular hora_fin si existe duración
                $horaFin = null;
                if (isset($primerServicio['hora_inicio']) && isset($primerServicio['duracion'])) {
                    $horaFin = \Carbon\Carbon::parse($primerServicio['hora_inicio'])
                        ->addMinutes($primerServicio['duracion'])
                        ->format('H:i:s');
                }
            }

            $pedido = Pedido::create([
                'id_local' => $id_local,
                'id_user' => auth()->id(),
                'nombre_cliente' => $this->nombre_cliente,
                'email' => $this->email,
                'telefono' => $this->telefono,
                'direccion' => $this->direccion,
                'ciudad' => $this->ciudad,
                'codigo_postal' => $this->codigo_postal,
                'notas_entrega' => $this->notas_entrega,
                'subtotal' => $this->subtotal,
                'envio' => $tieneProductos ? $this->envio : 0, // Sin envío para solo servicios
                'descuento' => $this->descuento,
                'total' => $this->total,
                'metodo_pago' => $this->metodo_pago,
                'crear_cuenta' => $this->crear_cuenta ?? false,
                'estado' => 'pendiente',
                'tipo_pedido' => $tipoPedido,
                'fecha_servicio' => $fechaServicio,
                'hora_inicio' => $horaInicio,
                'hora_fin' => $horaFin,
                'estado_reserva' => $tieneServicios ? 'pendiente' : null,
            ]);

            foreach ($this->items as $item) {
                $detalleData = [
                    'cantidad' => $item['quantity'],
                    'precio_unitario' => $item['price'],
                    'subtotal' => $item['price'] * $item['quantity'],
                    'variante' => $item['variant'] ?? null,
                ];

                if ($item['type'] === 'articulo') {
                    $detalleData['idarticulo'] = $item['id'];
                } elseif ($item['type'] === 'servicio') {
                    $detalleData['idservicio'] = $item['id'];

                    // Agregar información de reserva si existe
                    if (isset($item['id_empleado'])) {
                        $detalleData['id_empleado'] = $item['id_empleado'];
                    }
                    if (isset($item['fecha_servicio'])) {
                        $detalleData['fecha_reserva'] = $item['fecha_servicio'];
                    }
                    if (isset($item['hora_inicio'])) {
                        $detalleData['hora_reserva'] = $item['hora_inicio'];
                    }
                    if (isset($item['duracion'])) {
                        $detalleData['duracion_servicio'] = $item['duracion'];
                    }
                }

                $pedido->detalles()->create($detalleData);
            }

            // Limpiar tanto el carrito como la reserva directa
            session()->forget(['cart', 'direct_service_booking']);
            $this->items = [];

            return redirect()->route('checkout.confirmation', $pedido);
        } catch (\Exception $e) {
            session()->flash('error', 'Error al procesar el pedido: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.checkout')->layout('layouts.app');
    }
}
