<?php

namespace App\Http\Livewire\List;

use App\Models\Compra;
use Livewire\Component;

class ListCompras extends Component
{
    public  $busqueda;

    public function render()
    {
        $query = Compra::query();

        if ($this->busqueda) {
            $query->where('id', 'like', '%' . $this->busqueda . '%')
                ->orWhere('created_at', 'like', '%' . $this->busqueda . '%')
                ->orWhereHas('proveedor', function ($queryPersona) {
                    $queryPersona->where('nombre', 'like', '%' . $this->busqueda . '%');
                })
                ->orWhere('tipo_compra', 'like', '%' . $this->busqueda . '%')
                ->orWhere('tipo_pago', 'like', '%' . $this->busqueda . '%');
        }

        $compras = $query->with('proveedor')->orderBy('created_at', 'desc')->paginate(10);

        return view('livewire.list.list-compras', compact('compras'));
    }
}
