<?php

namespace App\Http\Livewire;

use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use App\Models\Categoria;
use Illuminate\Database\Eloquent\Builder;

class CategoriasTable extends DataTableComponent
{
    protected $model = Categoria::class;
    protected $listeners = ['refreshTable' => '$refresh'];

    public function builder(): Builder
    {
        $idLocal = auth()->user()->local->id;
        return Categoria::query()->where('id_local', $idLocal);
    }

    public function configure(): void
    {
        $this->setPrimaryKey('id_categoria');
        $this->setSearchEnabled(); // Habilitar la búsqueda
    }

    public function columns(): array
    {
        return [
            Column::make("Id categoria", "id_categoria")->sortable(),
            Column::make("Nombre", "nombre")->sortable()->searchable(),
            Column::make("Descripcion", "descripcion")->sortable(),
            Column::make("Estado", "estado")->sortable(),
            Column::make("Acciones")->label(
                fn($row, Column $column) => view('livewire.categorias.actions', ['row' => $row])
            ),
        ];
    }

    public function editar($id)
    {
        $categoria = Categoria::findOrFail($id);
        $this->emit('editarCategoria', $categoria->id_categoria);
    }

    public function borrar($id)
    {
        Categoria::find($id)->delete();
        session()->flash('message', 'Categoría eliminada exitosamente.');
    }
}
