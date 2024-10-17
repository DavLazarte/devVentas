<?php

namespace App\Http\Livewire\List;

use App\Models\Venta;
use Livewire\Component;

class ListVentas extends Component
{
    public  $busqueda, $ver_venta, $fecha;

    public $isOpen = 0;
    public $isLoading = false;
    public $idLocal;
    public $ventaIdToDelete;
    public $motivoCancelacion = '';

    protected $listeners = ['cancelarVenta', 'ver'];

    public function mount($fecha= null)
    {
        $this->idLocal = auth()->user()->local->id;
        // Asignar la fecha si se pasa
        $this->fecha = $fecha;
    }

    public function render()
    {
        return view('livewire.list.list-ventas');
    }

    public function ver($id)
    {
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
    



    public function cancelarVenta($id, $motivo)
    {
        $this->isLoading = true;
        
        
        $venta = Venta::findOrFail($id);

        // Cambiar el estado de la venta a "Cancelada" y guardar el motivo
        $venta->estado = 'Inactivo';
        $venta->motivo_cancelacion = $motivo; // Usar el argumento pasado
        $venta->save();

        $this->isLoading = false;
        
        session()->flash('message', 'Venta cancelada con éxito.');
        // Actualizamos la lista de ventas
        $this->emit('refreshDatatableVantas');
    }
}
