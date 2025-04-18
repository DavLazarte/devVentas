<?php

namespace App\Http\Livewire\List;

use Illuminate\Support\Facades\DB;
use App\Models\Venta;
use App\Models\Articulo;
use Livewire\Component;

class ListVentas extends Component
{
    public  $busqueda, $ver_venta, $fecha;

    public $isOpen = 0;
    public $isLoading = false;
    public $idLocal;
    public $ventaIdToDelete;
    public $motivoCancelacion = '';

    protected $listeners = [
        'cancelarVenta' => 'cancelarVenta',
        'ver'
    ];

    public function mount($fecha = null)
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


    // public function cancelarVenta($id, $motivo)
    // {

    //     $this->isLoading = true;


    //     $venta = Venta::findOrFail($id);

    //     // Cambiar el estado de la venta a "Cancelada" y guardar el motivo
    //     $venta->estado = 'Inactivo';
    //     $venta->motivo_cancelacion = $motivo; // Usar el argumento pasado
    //     $venta->save();

    //     $this->isLoading = false;

    //     session()->flash('message', 'Venta cancelada con éxito.');
    //     // Actualizamos la lista de ventas
    //     $this->emit('refreshDatatableVantas');
    // }


    public function cancelarVenta($id, $motivo)
    {
        $this->isLoading = true;

        DB::beginTransaction();

        try {
            $venta = Venta::findOrFail($id);

            foreach ($venta->detalles as $detalle) {
                $articulo = Articulo::find($detalle->idarticulo);
                if ($articulo) {
                    $articulo->stock += $detalle->cantidad;
                    $articulo->save();
                }
            }

            $venta->estado = 'Inactivo';
            $venta->motivo_cancelacion = $motivo;
            $venta->save();

            DB::commit();

            session()->flash('message', 'Venta con deuda cancelada con éxito y stock restaurado.');
            $this->emit('refreshDatatableVantas');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error al cancelar la venta: ' . $e->getMessage());
        }

        $this->isLoading = false;
    }
}
