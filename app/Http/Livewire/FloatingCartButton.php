<?php

namespace App\Http\Livewire;

use Livewire\Component;

class FloatingCartButton extends Component
{
    public $cartItemCount = 0;

    protected $listeners = ['cartUpdated' => 'updateCartItemCount'];

    public function mount()
    {
        // Initial count (if CartManager already has items on page load)
        // This might require accessing the CartManager state on mount, which can be tricky.
        // For simplicity, let's rely on the event for updates after page load.
        // A better approach would involve a global Cart service or state management.
    }

    public function updateCartItemCount($itemCount)
    {
        $this->cartItemCount = $itemCount;
    }

    public function render()
    {
        return view('livewire.floating-cart-button');
    }
}
