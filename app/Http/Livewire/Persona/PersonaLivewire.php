<?php

namespace App\Http\Livewire\Persona;

use App\Models\Persona;
use Livewire\Component;


class PersonaLivewire extends Component
{
    public $nombre, $tipo_persona, $direccion, $mail, $estado, $telefono, $busqueda, $persona_id;
    public $isOpen = 0;

    public function render()
    {
        $query = Persona::query();

        if ($this->busqueda) {
            $query->where('idpersona', 'like', '%' . $this->busqueda . '%')
                ->orWhere('nombre', 'like', '%' . $this->busqueda . '%')
                ->orWhere('tipo_persona', 'like', '%' . $this->busqueda . '%');
        }

        $persona = $query->paginate(10);
        // $this->categorias = Categoria::pluck('nombre', 'id_categoria');

        return view('livewire.persona.persona-livewire', compact('persona'));
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

        Persona::updateOrCreate(['idpersona' => $this->persona_id], [
            'tipo_persona' => $this->tipo_persona,
            'nombre' => $this->nombre,
            'direccion' => $this->direccion,
            'mail' => $this->mail,
            'telefono' => $this->telefono,
            // 'estado' => $this->estado,
        ]);

        session()->flash(
            'message',
            $this->persona_id ? 'Persona actualizado exitosamente.' : 'Persona creada exitosamente.'
        );

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
    }
}
