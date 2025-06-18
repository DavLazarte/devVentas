<?php

namespace App\Http\Livewire;

use Livewire\Component;

class FooterMenu extends Component
{
    public $items = [];
    public $showCart = false;

    protected $listeners = ['cartItemAdded' => 'updateCartCount'];

    public function mount()
    {
        $this->loadCart();
    }

    public function loadCart()
    {
        $this->items = session()->get('cart', []);
    }

    public function updateCartCount()
    {
        $this->loadCart();
    }

    public function toggleCart()
    {
        $this->showCart = !$this->showCart;
    }

    public function proceedToCheckout()
    {
        if (count($this->items) === 0) {
            return;
        }

        $this->showCart = false;
        return redirect()->route('checkout');
    }

    public function render()
    {
        return view('components.footer-menu');
    }
}
