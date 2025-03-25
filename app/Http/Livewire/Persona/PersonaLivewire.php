<?php

namespace App\Http\Livewire\Persona;

use App\Models\Persona;
use Livewire\Component;


class PersonaLivewire extends Component
{
    public $nombre, $tipo_persona, $monto, $dni_cuit, $direccion, $descripcion, $ingreso_id, $saldo, $mail, $estado, $telefono, $busqueda, $persona_id, $personaId, $idpersona, $clienteSeleccionado, $nombre_cliente, $totalSaldos, $ventasConSaldos;
    public $isOpen = 0;
    public $isOpenIngreso = 0, $idpersonaPagos = null;
    public $saldos = [];
    public $searchCliente = '';
    public $loading = false;

    protected $listeners = [
        'editarPersona' => 'editar',
        'openModal' => 'openModal',
        'verPagos' => 'mostrarPagos',
        'NoVerPagos' => 'ocultarPagos'
    ];
    public function render()
    {
        return view('livewire.persona.persona-livewire');
    }

    public function crear()
    {
        $this->resetInputFields();
        $this->openModal();
    }

    public function openModal()
    {
        $this->resetValidation();
        $this->isOpen = true;
    }

    public function closeModal()
    {
        $this->isOpen = false;
    }

    private function resetInputFields()
    {
        $this->nombre = '';
        $this->tipo_persona = '';
        $this->telefono = '';
        $this->direccion = '';
        $this->persona_id = '';
        $this->mail = '';
        $this->estado = '';
        $this->monto = '';
        $this->descripcion = '';
        $this->estado = '';
        $this->idpersona = '';
        $this->saldo = '';
        $this->nombre_cliente = '';
        $this->clienteSeleccionado = '';
        $this->totalSaldos = '';
        $this->dni_cuit = '';
        // $this->ver_venta = '';

    }

    public function guardarPersona()
    {
        $this->loading = true;
        // sleep(2);
        $this->validate([
            'nombre' => 'required',
            'tipo_persona' => 'required',
        ]);

        try {


            $idLocal = auth()->user()->local->id;

            Persona::updateOrCreate(['idpersona' => $this->persona_id], [
                'tipo_persona' => $this->tipo_persona,
                'nombre' => $this->nombre,
                'dni_cuit' => $this->dni_cuit,
                'direccion' => $this->direccion,
                'mail' => $this->mail,
                'telefono' => $this->telefono,
                'id_local' => $idLocal,
                // 'estado' => $this->estado,
            ]);

            session()->flash(
                'message',
                $this->persona_id ? 'Persona actualizado exitosamente.' : 'Persona creada exitosamente.'
            );

            $this->emit('refreshTablePersonas');
            $this->closeModal();
            $this->resetInputFields();
        } catch (\Exception $e) {
            $this->emit('error', 'Ocurrió un error al guardar la persona: ' . $e->getMessage());
        } finally {
            $this->loading = false; // Reactiva el botón
        }
    }

    public function editar($id)
    {
        $persona = Persona::findOrFail($id);
        $this->persona_id = $id;
        $this->tipo_persona = $persona->tipo_persona;
        $this->nombre = $persona->nombre;
        $this->dni_cuit = $persona->dni_cuit;
        $this->direccion = $persona->direccion;
        $this->telefono = $persona->telefono;
        $this->mail = $persona->mail;
        $this->estado = $persona->estado;

        $this->openModal();
    }

    public function borrar($id)
    {
        Persona::find($id)->delete();
        session()->flash('message', 'Persona eliminado exitosamente.');
        $this->emit('refreshTablePersonas');
    }
    public function mostrarPagos($idpersona)
    {
        $this->idpersonaPagos = $idpersona;
        $this->emit('agregarCliente', $idpersona); // Asigna el id de la persona para mostrar los pagos
    }

    public function ocultarPagos()
    {
        $this->idpersonaPagos = null;  // Limpia la variable cuando quieras ocultar el componente
    }

    public function guardar()
    {
        $this->emit('guardarPago');
    }
    public function generarPdf()
    {
        $this->emit('descargarPdf');
    }
}
