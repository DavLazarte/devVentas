<?php

namespace App\Http\Livewire\List;

use App\Models\Venta;
use Livewire\Component;

class ListVentas extends Component
{
    public  $busqueda,$ver_venta,$fecha;

    public $isOpen = 0;
    public $idLocal;
    public $ventaIdToDelete;

    protected $listeners = ['cancelarVenta'];

    public function mount(){
        $this->idLocal = auth()->user()->local->id;

    }

    public function render()
    {
        $query = Venta::where('id_local', $this->idLocal);

        if ($this->busqueda) {
            $query->where('id', 'like', '%' . $this->busqueda . '%')
                ->orWhere('created_at', 'like', '%' . $this->busqueda . '%')
                ->orWhereHas('persona', function ($queryPersona) {
                    $queryPersona->where('nombre', 'like', '%' . $this->busqueda . '%');
                })
                ->orWhere('tipo_venta', 'like', '%' . $this->busqueda . '%')
                ->orWhere('forma_de_pago', 'like', '%' . $this->busqueda . '%');
        }
        if ($this->fecha) {
            $query->whereDate('created_at', $this->fecha);
        }

        $ventas = $query->with('persona')->orderBy('created_at', 'desc')->paginate(10);

        return view('livewire.list.list-ventas', compact('ventas'));
    }
    
    public function ver ($id){
        $this->ver_venta = Venta::with('detalles.producto', 'persona')->findOrFail($id);
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


    public function cancelarVenta($id)
    {
        $venta = Venta::findOrFail($id);

        // Cambiar el estado de la venta a "inactivo"
        $venta->estado = 'inactivo';
        $venta->save();

        // Opcionalmente, puedes hacer un soft delete en lugar de cambiar el estado
        // $venta->delete();

        // Actualizar la lista de ventas después de cancelar la venta
        $ventas = Venta::where('id_local', $this->idLocal)
        ->orderBy('created_at', 'desc')->get();
    }

}
