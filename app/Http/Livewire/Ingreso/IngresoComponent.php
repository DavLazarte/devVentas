<?php

namespace App\Http\Livewire\Ingreso;

use App\Models\Ingreso;
use App\Models\Persona;
use App\Models\Venta;
use Livewire\Component;


class IngresoComponent extends Component
{
    public $personas, $monto, $saldo, $descripcion, $estado, $busqueda, $idpersona, $clienteSeleccionado, $nombre_cliente, $ingreso_id, $personaId, $totalSaldos;
    public $isOpen = 0;
    public $idLocal;
    public $isOpenIngreso = 0;
    public $showVentas = 0;
    public $modoEdit = 0;
    public $searchCliente = '';
    public $saldos = [];
    public $ver_venta;
    public $detallesVisibles = [];

    protected $listeners = ['pagoGuardado' => 'handlePagoGuardado'];


    public function handlePagoGuardado()
    {
        $this->closeModal();
        $this->clienteSeleccionado = null; // Limpia la variable clienteSeleccionado
        // También puedes limpiar otros campos si es necesario
    }

    public function mount()
    {
        $this->idLocal = auth()->user()->local->id;
    }


    public function render()
    {
        $this->filtrarCliente();
        return view('livewire.ingreso.ingreso-component', [
            'personas' => $this->personas
        ]);
    }

    public function filtrarCliente()
    {
        $this->personas = Persona::where('id_local', $this->idLocal);  // Comienzas construyendo la consulta con la restricción de local.

        if ($this->searchCliente) {
            $this->personas->where('idpersona', 'like', '%' . $this->searchCliente . '%')
                ->orWhere('nombre', 'like', '%' . $this->searchCliente . '%');
        }

        $this->personas = $this->personas->get();
    }

    public function agregarCliente($id)
    {
        $this->emit('agregarCliente', $id);
        $this->searchCliente = '';
        $this->clienteSeleccionado = true;

    }
    public function crear()
    {
        $this->resetInputFields();
        $this->openModal();
    }

    public function openModal()
    {
        $this->isOpen = true;
    }

    public function closeModal()
    {
        $this->isOpen = false;
        $this->modoEdit = false;
    }

    private function resetInputFields()
    {
        $this->monto = '';
        $this->descripcion = '';
        $this->estado = '';
        $this->idpersona = '';
        $this->saldo = '';
        $this->nombre_cliente = '';
        $this->clienteSeleccionado = '';
        $this->totalSaldos = '';
        $this->ver_venta = '';
    }
    public function guardar()
    {
        $this->emit('guardarPago');
    }


    public function editar($id)
    {
        $this->modoEdit = true;
        $ingreso = Ingreso::findOrFail($id);
        $this->ingreso_id = $id;
        $this->idpersona = $ingreso->idpersona;
        $this->monto = $ingreso->monto;
        $this->descripcion = $ingreso->descripcion;
        $this->saldo = $ingreso->saldo;

        $this->openModal();
    }

    public function borrar($id)
    {
        Ingreso::find($id)->delete();
        session()->flash('message', 'ingreso eliminado exitosamente.');
        $this->emit('refreshDatatableIngresos');
    }
   
    public function ver($id)
    {
        $this->ver_venta = Venta::with('detalles.producto', 'persona')->findOrFail($id);
        $this->showVentas = true;
    }
}
