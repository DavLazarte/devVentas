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
        'metodo_pago' => 'required|in:efectivo,tarjeta',
        'acepto_terminos' => 'required|accepted',
    ];

    protected $messages = [
        'nombre_cliente.required' => 'El nombre es obligatorio',
        'nombre_cliente.min' => 'El nombre debe tener al menos 3 caracteres',
        'email.required' => 'El email es obligatorio',
        'email.email' => 'El email debe ser válido',
        'telefono.required' => 'El teléfono es obligatorio',
        'metodo_pago.required' => 'Debes seleccionar un método de pago',
        'metodo_pago.in' => 'El método de pago seleccionado no es válido',
        'acepto_terminos.required' => 'Debes aceptar los términos y condiciones',
        'acepto_terminos.accepted' => 'Debes aceptar los términos y condiciones',
    ];

    public function mount()
    {
        // Forzar lectura fresca de la sesión
        // session()->reflash();
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

        // Ejecutando validación
        try {
            $this->validate();
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('DEBUG-CHECKOUT: Fallo de validación. Los errores no deberían ser silenciosos: ' . json_encode($e->errors()));
            throw $e;
        }



        try {
            // Verificar que hay items
            if (empty($this->items)) {
                session()->flash('error', 'No hay elementos para procesar');
                return;
            }

            // Obtener id_local del primer item (como en Ventas)
            $primerItem = reset($this->items);
            $id_local = $primerItem['shop_id'] ?? null;

            if (!$id_local) {
                session()->flash('error', 'Error al procesar el pedido: información de tienda no disponible');
                return;
            }



            // Determinar tipo de pedido
            $tieneServicios = collect($this->items)->contains('type', 'servicio');
            $tieneProductos = collect($this->items)->contains('type', 'articulo');
            $tipoPedido = $tieneServicios && $tieneProductos ? 'mixto' : ($tieneServicios ? 'servicio' : 'producto');

            // Lógica para servicios y reservas
            $fechaServicio = null;
            $horaInicio = null;
            $estadoReserva = null;

            $primerServicio = collect($this->items)->firstWhere('type', 'servicio');
            if ($primerServicio) {
                $fechaServicio = $primerServicio['fecha_servicio'] ?? null;
                $horaInicio = $primerServicio['hora_inicio'] ?? null;
                $estadoReserva = ($fechaServicio || $horaInicio) ? 'pendiente' : null;
            }

            $pedido = Pedido::create([
                'id_local' => $id_local,
                'id_user' => Auth::id() ?? null,
                'nombre_cliente' => $this->nombre_cliente,
                'email' => $this->email,
                'telefono' => $this->telefono,
                'direccion' => $this->direccion,
                'ciudad' => $this->ciudad,
                'codigo_postal' => $this->codigo_postal,
                'notas_entrega' => $this->notas_entrega,
                'subtotal' => $this->subtotal,
                'envio' => $this->envio,
                'descuento' => $this->descuento,
                'total' => $this->total,
                'estado' => 'pendiente',
                'metodo_pago' => $this->metodo_pago,
                'crear_cuenta' => $this->crear_cuenta,
                'tipo_pedido' => $tipoPedido,
                'fecha_servicio' => $fechaServicio,
                'hora_inicio' => $horaInicio,
                'estado_reserva' => $estadoReserva,
            ]);



            foreach ($this->items as $item) {


                $detalleData = [
                    'pedido_id' => $pedido->id,
                    'cantidad' => $item['quantity'],
                    'precio_unitario' => $item['price'],
                    'subtotal' => $item['price'] * $item['quantity'],
                    'sku_vendido' => $item['sku'] ?? null,
                ];

                if ($item['type'] === 'articulo') {
                    $detalleData['idarticulo'] = $item['id'];

                    // Manejar variantes igual que en Ventas
                    if (isset($item['id_variante'])) {
                        $detalleData['id_variante'] = $item['id_variante'];
                        $detalleData['descripcion_variante'] = $item['variant'] ?? null;
                    }
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


            // Limpiar tanto el carrito como la reserva directa ANTES de redirigir
            session()->forget(['cart', 'direct_service_booking']);
            session()->save(); // Asegurate que se guarde inmediatamente

            $this->items = [];

            return redirect()->route('checkout.confirmation', $pedido);
        } catch (\Exception $e) {
            // DEBUG: Se ha capturado una excepción.
            Log::error('DEBUG-CHECKOUT: ¡Excepción capturada! Error: ' . $e->getMessage());
            Log::error('DEBUG-CHECKOUT: Fila del error: ' . $e->getLine());
            session()->flash('error', 'Error al procesar el pedido: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.checkout')->layout('layouts.app');
    }
}
