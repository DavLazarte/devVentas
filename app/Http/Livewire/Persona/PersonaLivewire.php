<?php

namespace App\Http\Livewire\Persona;

use App\Models\Persona;
use Livewire\Component;


class PersonaLivewire extends Component
{
    public $nombre, $tipo_persona, $direccion, $mail, $estado, $telefono, $busqueda, $persona_id;
    public $isOpen = 0;

    protected $listeners = [
        'editarPersona' => 'editar',
        'openModal' => 'openModal'
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
    }

    public function guardar()
    {
        $this->validate([
            'nombre' => 'required',
            'tipo_persona' => 'required',
        ]);

        $idLocal = auth()->user()->local->id;

        Persona::updateOrCreate(['idpersona' => $this->persona_id], [
            'tipo_persona' => $this->tipo_persona,
            'nombre' => $this->nombre,
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
    }

    public function editar($id)
    {
        $persona = Persona::findOrFail($id);
        $this->persona_id = $id;
        $this->tipo_persona = $persona->tipo_persona;
        $this->nombre = $persona->nombre;
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
}
