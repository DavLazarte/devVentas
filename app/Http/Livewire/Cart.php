<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Articulo;

class Cart extends Component
{
    public $items = [];
    public $total = 0;

    protected $listeners = [
        'cartItemAdded' => 'loadCart',
        'proceedToCheckout' => 'proceedToCheckout'
    ];

    public function mount()
    {
        $this->loadCart();
    }

    public function loadCart()
    {
        $this->items = session()->get('cart', []);
        $this->calculateTotal();
    }

    public function calculateTotal()
    {
        $this->total = 0;
        foreach ($this->items as $item) {
            $this->total += $item['price'] * $item['quantity'];
        }
    }

    public function updateQuantity($key, $quantity)
    {
        if ($quantity > 0) {
            $this->items[$key]['quantity'] = $quantity;
            session()->put('cart', $this->items);
            $this->calculateTotal();
            $this->emit('cartItemAdded', count($this->items));
        }
    }

    public function removeItem($key)
    {
        unset($this->items[$key]);
        session()->put('cart', $this->items);
        $this->calculateTotal();
        $this->emit('cartItemAdded', count($this->items));
    }

    // Cart.php (proceedToCheckout method)
    public function proceedToCheckout()
    {
        if (count($this->items) === 0) {
            return;
        }

        // Asegurate que la sesión está actualizada antes de redirigir
        session()->put('cart', $this->items);
        session()->forget('direct_service_booking');
        session()->save(); // <--- GOOD: Explicitly saves the session

        $this->emit('closeCart');
        return redirect()->route('checkout');
    }

    private function getProductDetails($productId)
    {
        $product = Articulo::find($productId);

        if (!$product) {
            return null;
        }

        return [
            'id' => $product->idarticulo,
            'nombre' => $product->nombre,
            'precio_unitario' => $product->precio_unitario,
            'imagen_url' => $product->imagen_url
        ];
    }

    public function render()
    {
        return view('livewire.cart');
    }
}
