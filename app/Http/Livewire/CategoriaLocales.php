<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Category;

class CategoriaLocales extends Component
{
    public $categoria;
    public $locales;

    public function mount($slug)
    {
        $this->categoria = Category::where('slug', $slug)->firstOrFail();
        $this->locales = $this->categoria->locales()
            ->where('estado', 'activo')
            ->with('user') // si querés info del dueño
            ->get();
    }
    public function render()
    {
        return view('livewire.categoria-locales');
    }
}
