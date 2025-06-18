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
        // Obtener items del carrito desde la sesión
        $this->items = session('cart', []);

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
                session()->flash('error', 'El carrito está vacío');
                return;
            }

            $primerItem = reset($this->items);
            $id_local = $primerItem['shop_id'] ?? null;

            if (!$id_local) {
                session()->flash('error', 'Error al procesar el pedido: información de tienda no disponible');
                return;
            }

            $this->validate([
                'nombre_cliente' => 'required|min:3',
                'email' => 'required|email',
                'telefono' => 'required',
                'direccion' => 'required',
                'ciudad' => 'required',
                'codigo_postal' => 'required',
                'metodo_pago' => 'required',
                'acepto_terminos' => 'required|accepted'
            ], [
                'nombre_cliente.required' => 'El nombre es obligatorio',
                'email.required' => 'El email es obligatorio',
                'email.email' => 'El email debe ser válido',
                'telefono.required' => 'El teléfono es obligatorio',
                'direccion.required' => 'La dirección es obligatoria',
                'ciudad.required' => 'La ciudad es obligatoria',
                'codigo_postal.required' => 'El código postal es obligatorio',
                'metodo_pago.required' => 'Debes seleccionar un método de pago',
                'acepto_terminos.required' => 'Debes aceptar los términos y condiciones',
                'acepto_terminos.accepted' => 'Debes aceptar los términos y condiciones'
            ]);

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
                'envio' => $this->envio,
                'descuento' => $this->descuento,
                'total' => $this->total,
                'metodo_pago' => $this->metodo_pago,
                'crear_cuenta' => $this->crear_cuenta,
                'estado' => 'pendiente'
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
                }

                $pedido->detalles()->create($detalleData);
            }

            session()->forget('cart');
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
