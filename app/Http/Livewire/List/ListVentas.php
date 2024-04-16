<?php

namespace App\Http\Livewire\List;

use App\Models\Venta;
use Livewire\Component;

class ListVentas extends Component
{
    public  $busqueda;

    public function render()
    {
        $idLocal = auth()->user()->local->id;
        $query = Venta::where('id_local', $idLocal);

        if ($this->busqueda) {
            $query->where('id', 'like', '%' . $this->busqueda . '%')
                ->orWhere('created_at', 'like', '%' . $this->busqueda . '%')
                ->orWhereHas('persona', function ($queryPersona) {
                    $queryPersona->where('nombre', 'like', '%' . $this->busqueda . '%');
                })
                ->orWhere('tipo_venta', 'like', '%' . $this->busqueda . '%')
                ->orWhere('forma_de_pago', 'like', '%' . $this->busqueda . '%');
        }

        $ventas = $query->with('persona')->orderBy('created_at', 'desc')->paginate(10);

        return view('livewire.list.list-ventas', compact('ventas'));
    }
}
