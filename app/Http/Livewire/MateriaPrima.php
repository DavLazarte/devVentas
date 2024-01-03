<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\MateriaPrimaModel;

class MateriaPrima extends Component
{
    public  $producto, $precio, $peso, $stock, $id_materia, $busqueda;
    public $modal = false;

    use WithPagination;

    // public function render()
    // {
    //     return view('livewire.materia-prima',[
    //         'materia' => MateriaPrimaModel::paginate(10),
    //     ]);
    // }
    public function render()
    {
        $query = MateriaPrimaModel::query();

        if ($this->busqueda) {
            $query->where('id', 'like', '%' . $this->busqueda . '%')
                ->orWhere('producto', 'like', '%' . $this->busqueda . '%');
        }

        $materia = $query->paginate(10);

        return view('livewire.materia-prima', compact('materia'));
    }

    public function buscar()
    {
        $this->resetPage();
    }
    public function crear()
    {
        $this->limpiarCampos();
        $this->abrirModal();
    }

    public function abrirModal()
    {
        $this->modal = true;
    }
    public function cerrarModal()
    {
        $this->modal = false;
    }
    public function limpiarCampos()
    {
        $this->producto = '';
        $this->precio = '';
        $this->peso = '';
        $this->stock = '';
        $this->id_materia = '';
    }
    public function editar($id)
    {
        $producto = MateriaPrimaModel::findOrFail($id);
        $this->id_materia = $id;
        $this->producto = $producto->producto;
        $this->precio = $producto->precio;
        $this->peso = $producto->peso;
        $this->stock = $producto->stock;
        $this->abrirModal();
    }

    public function borrar($id)
    {
        MateriaPrimaModel::find($id)->delete();
        session()->flash('message', 'Registro eliminado correctamente');
    }

    public function guardar()
    {
        MateriaPrimaModel::updateOrCreate(
            ['id' => $this->id_materia],
            [
                'producto' => $this->producto,
                'precio' => $this->precio,
                'peso' => $this->peso,
                'stock' => $this->stock
            ]
        );

        session()->flash(
            'message',
            $this->id_materia ? '¡Actualización exitosa!' : '¡Alta Exitosa!'
        );

        $this->cerrarModal();
        $this->limpiarCampos();
    }
}
