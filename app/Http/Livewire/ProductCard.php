<?php

namespace App\Http\Livewire;

use Livewire\Component;

class ProductCard extends Component
{
    public $product;
    public $type;
    public $showShop;
    public $showFavorite;
    public $showAddButton;
    public $quantity = 1;

    public function mount($product, $type = 'articulo', $showShop = true, $showFavorite = true, $showAddButton = true)
    {
        $this->product = $product;
        $this->type = $type;
        $this->showShop = $showShop;
        $this->showFavorite = $showFavorite;
        $this->showAddButton = $showAddButton;
    }

    public function updateQuantity($id, $newQuantity)
    {
        if ($newQuantity > 0) {
            $this->quantity = $newQuantity;
        }
    }

    public function addToCart($id, $quantity)
    {
        $cart = session()->get('cart', []);
        $itemKey = $this->type === 'articulo' ? 'product_' . $id : 'service_' . $id;

        // Verificar si el carrito está vacío
        if (empty($cart)) {
            $cart[$itemKey] = [
                'id' => $id,
                'type' => $this->type,
                'name' => $this->product->nombre,
                'price' => $this->product->precio_unitario,
                'quantity' => $quantity,
                'image' => $this->product->imagen_url,
                'shop_id' => $this->product->local->id,
                'shop' => $this->showShop ? $this->product->local->nombre : null
            ];
            session()->put('cart', $cart);
            $this->emit('cartItemAdded', count($cart));
            return;
        }

        // Obtener el ID del local del primer producto en el carrito
        $firstItem = reset($cart);
        $currentShopId = $firstItem['shop_id'];

        // Verificar si el producto que se intenta agregar es del mismo local
        if ($this->product->local->id !== $currentShopId) {
            $this->dispatchBrowserEvent('showAlert', [
                'type' => 'warning',
                'title' => '¡Atención!',
                'message' => 'Por favor, finaliza el pedido actual antes de agregar productos de otro local.'
            ]);
            return;
        }

        // Si es del mismo local, proceder con la adición
        if (isset($cart[$itemKey])) {
            $cart[$itemKey]['quantity'] += $quantity;
        } else {
            $cart[$itemKey] = [
                'id' => $id,
                'type' => $this->type,
                'name' => $this->product->nombre,
                'price' => $this->product->precio_unitario,
                'quantity' => $quantity,
                'image' => $this->product->imagen_url,
                'shop_id' => $this->product->local->id,
                'shop' => $this->showShop ? $this->product->local->nombre : null
            ];
        }

        session()->put('cart', $cart);
        $this->emit('cartItemAdded', count($cart));
    }

    public function render()
    {
        return view('components.product-card');
    }
}
