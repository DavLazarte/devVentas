<?php

namespace App\Http\Livewire\Articulo;

use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use App\Models\Articulo;
use Illuminate\Database\Eloquent\Builder;

class ArticulosTable extends DataTableComponent
{
    protected $model = Articulo::class;
    protected $listeners = ['refreshDatatableArticulos' => '$refresh'];

    public function builder(): Builder
    {
        $idLocal = auth()->user()->local->id;

        // Construir la consulta de artículos pertenecientes al local del usuario
        $query = Articulo::where('articulos.id_local', $idLocal);

        // Obtener los artículos con sus categorías
        return $query->with('categoria');
    }

    public function configure(): void
    {
        $this->setPrimaryKey('idarticulo');
        $this->setSearchEnabled(); // Habilitar la búsqueda
    }


    public function columns(): array
    {
        return [
            Column::make("Id", "idarticulo")->sortable(),
            Column::make("Categoría", "categoria.nombre")
                ->sortable()
                ->searchable(),
            Column::make("Codigo", "codigo")
                ->sortable()
                ->searchable(),
            Column::make("Nombre", "nombre")
                ->sortable()
                ->searchable(),
            Column::make("Stock", "stock")
                ->sortable(),
            Column::make("Descripcion", "descripcion")
                 ->sortable(),
            Column::make("Precio", "precio_unitario")
                ->sortable(),
            Column::make("Estado", "estado")
                ->sortable(),
            Column::make("Acciones")    
                ->label(
                    fn ($row, Column $column) => view('livewire.articulo.actions', ['row' => $row])
                ),
        ];
    }

    public function editar($id)
    {
        $articulo = Articulo::findOrFail($id);
        $this->emit('editarArticulo', $articulo->idarticulo);
    }

    public function borrar($id)
    {
        Articulo::find($id)->delete();
        session()->flash('message', 'Producto eliminado exitosamente.');
    }
}
