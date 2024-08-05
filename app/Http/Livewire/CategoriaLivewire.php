<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Categoria;

class CategoriaLivewire extends Component
{
    public $categorias, $categoria_id, $nombre, $descripcion, $estado, $busqueda;
    public $isOpen = 0;

    protected $listeners = [
        'editarCategoria' => 'editar',
        'openModal' => 'openModal'
    ];

    public function render()
    {
        return view('livewire.categorias.categoria-livewire');
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
        $this->descripcion = '';
        $this->estado = '';
        $this->categoria_id = '';
    }

    public function guardar()
    {
        $this->validate([
            'nombre' => 'required',
            'descripcion' => 'required',
            'estado' => 'required|in:activo,inactivo',
        ]);

        $idLocal = auth()->user()->local->id;

        Categoria::updateOrCreate(['id_categoria' => $this->categoria_id], [
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'estado' => $this->estado,
            'id_local' => $idLocal,
        ]);

        session()->flash(
            'message',
            $this->categoria_id ? 'Categoría actualizada exitosamente.' : 'Categoría creada exitosamente.'
        );
        $this->emit('refreshTable');
        $this->closeModal();
        $this->resetInputFields();
    }

    public function editar($id)
    {
        $categoria = Categoria::findOrFail($id);
        $this->categoria_id = $id;
        $this->nombre = $categoria->nombre;
        $this->descripcion = $categoria->descripcion;
        $this->estado = $categoria->estado;

        $this->openModal();
    }

    public function borrar($id)
    {
        Categoria::find($id)->delete();
        session()->flash('message', 'Categoría eliminada exitosamente.');
        $this->emit('refreshTable');
    }
}
