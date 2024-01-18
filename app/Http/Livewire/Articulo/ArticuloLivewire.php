<?php

namespace App\Http\Livewire\Articulo;

use Livewire\Component;
use App\Models\Articulo;
use App\Models\Categoria;


class ArticuloLivewire extends Component
{

    public $articulos, $articulo_id, $nombre, $descripcion, $estado, $stock, $codigo, $precio_unitario, $busqueda, $categorias,$categoria_id;
    public $isOpen = 0;
    // En tus componentes Livewire (por ejemplo, CategoriaLivewire)
    public $layout = 'sistema';


    public function render()
    {
        $query = Articulo::query();

        if ($this->busqueda) {
            $query->where('idarticulo', 'like', '%' . $this->busqueda . '%')
                ->orWhere('nombre', 'like', '%' . $this->busqueda . '%')
                ->orWhere('codigo', 'like', '%' . $this->busqueda . '%');
        }

        $articulo = $query->with('categoria')->paginate(10);
        $this->categorias = Categoria::pluck('nombre', 'id_categoria');

        return view('livewire.articulo.articulo-livewire', compact('articulo'));
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
        $this->articulo_id = '';
        $this->stock = '';
        $this->precio_unitario = '';
        $this->codigo = '';
    }

    public function guardar()
    {
        $this->validate([
            'nombre' => 'required',
            'descripcion' => 'required',
            'codigo' => 'required',
            'estado' => 'required|in:activo,inactivo',
        ]);

        Articulo::updateOrCreate(['idarticulo' => $this->articulo_id], [
            'idcategoria' => $this->categoria_id,
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'codigo' => $this->codigo,
            'precio_unitario' => $this->precio_unitario,
            'stock' => $this->stock,
            'estado' => $this->estado,
        ]);

        session()->flash(
            'message',
            $this->articulo_id ? 'Articulo actualizado exitosamente.' : 'Articulo creado exitosamente.'
        );

        $this->closeModal();
        $this->resetInputFields();
    }

    public function editar($id)
    {
        $articulo = Articulo::findOrFail($id);
        $this->articulo_id = $id;
        // $this->categorias = $articulo->categoria->nombre;
        $this->categoria_id = $articulo->idcategoria;
        $this->nombre = $articulo->nombre;
        $this->descripcion = $articulo->descripcion;
        $this->codigo = $articulo->codigo;
        $this->precio_unitario = $articulo->precio_unitario;
        $this->stock = $articulo->stock;
        $this->estado = $articulo->estado;

        $this->openModal();
    }

    public function borrar($id)
    {
        Articulo::find($id)->delete();
        session()->flash('message', 'Articulo eliminado exitosamente.');
    }
}
