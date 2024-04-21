<?php

namespace App\Http\Livewire\List;

use App\Models\Compra;
use Livewire\Component;

class ListCompras extends Component
{
    public  $busqueda,$fecha, $ver_compra;
    public $isOpen =0;
    public $idLocal =0;

    protected $listeners = ['cancelarCompra'];

    public function mount(){
        $this->idLocal = auth()->user()->local->id;
    }

    public function render()
    {
        
        $query = Compra::where('id_local', $this->idLocal);

        if ($this->busqueda) {
            $query->where('id', 'like', '%' . $this->busqueda . '%')
                ->orWhere('created_at', 'like', '%' . $this->busqueda . '%')
                ->orWhereHas('proveedor', function ($queryPersona) {
                    $queryPersona->where('nombre', 'like', '%' . $this->busqueda . '%');
                })
                ->orWhere('tipo_compra', 'like', '%' . $this->busqueda . '%')
                ->orWhere('tipo_pago', 'like', '%' . $this->busqueda . '%');
        }
        if ($this->fecha) {
            $query->whereDate('created_at', $this->fecha);
        }

        $compras = $query->with('proveedor')->orderBy('created_at', 'desc')->paginate(10);

        return view('livewire.list.list-compras', compact('compras'));
    }
    public function ver ($id){
        $this->ver_compra = Compra::with('detallesCompra.articulo', 'proveedor')
        ->findOrFail($id);
        $this->openModal();
    }


    public function openModal()
    {
        $this->isOpen = true;
    }

    public function closeModal()
    {
        $this->isOpen = false;

    }
    public function cancelarCompra($id)
    {
        $compra = Compra::findOrFail($id);

        // Cambiar el estado de la venta a "inactivo"
        $compra->estado = 'inactivo';
        $compra->save();

        // Opcionalmente, puedes hacer un soft delete en lugar de cambiar el estado
        // $compra->delete();

        // Actualizar la lista de ventas después de cancelar la venta
        $compras = Compra::where('id_local', $this->idLocal)
        ->orderBy('created_at', 'desc')->get();
    }
}
