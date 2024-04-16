<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Categoria;

class CategoriaLivewire extends Component
{
    public $categorias, $categoria_id, $nombre, $descripcion, $estado, $busqueda;
    public $isOpen = 0;

    public function render()
    {

        $idLocal = auth()->user()->local->id;


        $query = Categoria::where('id_local',$idLocal);;

        if ($this->busqueda) {
            $query->where('id_categoria', 'like', '%' . $this->busqueda . '%')
                ->orWhere('nombre', 'like', '%' . $this->busqueda . '%');
        }

        $categoria = $query->paginate(10);
    

        return view('livewire.categorias.categoria-livewire', compact('categoria'));
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
    }
}
