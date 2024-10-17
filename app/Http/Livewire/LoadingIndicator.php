<?php

namespace App\Http\Livewire;

use Livewire\Component;

class LoadingIndicator extends Component
{
    public $isLoading = false;

    protected $listeners = ['startLoading', 'stopLoading'];

    public function startLoading()
    {
        $this->isLoading = true;
    }

    public function stopLoading()
    {
        $this->isLoading = false;
    }

    public function render()
    {
        return view('livewire.loading-indicator');
    }
}
