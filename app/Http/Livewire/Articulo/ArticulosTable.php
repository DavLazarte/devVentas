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

        // Ordenar por ID descendente para ver los más recientes primero
        return Articulo::where('articulos.id_local', $idLocal)
            ->with('categoria')
            ->select('articulos.*')
            ->orderByDesc('idarticulo');
    }

    public function configure(): void
    {
        $this->setPrimaryKey('idarticulo');
        $this->setSearchEnabled(); // Habilitar la búsqueda

    }



    public function columns(): array
    {
        return [

            Column::make("Id", "idarticulo")
                ->sortable()
                ->collapseOnMobile(),
            Column::make("Categoría", "categoria.nombre")
                ->sortable()
                ->searchable()
                ->collapseOnMobile(),
            Column::make("Codigo", "codigo")
                ->sortable()
                ->searchable()
                ->collapseOnMobile(),
            Column::make("Nombre", "nombre")
                ->sortable()
                ->searchable(),
            Column::make("Imagen", "imagen")
                ->label(fn($row) => view('livewire.articulo.imagen', ['imagen' => $row->imagen])),
            Column::make("Stock", "stock")
                ->sortable()
                ->collapseOnMobile(),
            Column::make("Descripcion", "descripcion")
                ->sortable()
                ->collapseOnMobile(),
            Column::make("Precio", "precio_unitario")
                ->sortable(),
            Column::make("Destacado", "destacado")
                ->sortable()
                ->collapseOnMobile()
                ->format(function ($row) {
                    return $row
                        ? '<span class="text-green-600">✅</span>'
                        : '<span class="text-red-600">❌</span>';
                })
                ->html(),
            Column::make("Estado", "estado")
                ->sortable()
                ->collapseOnMobile()
                ->format(function ($row) {
                    return $row
                        ? '<span class="text-green-600">✅</span>'
                        : '<span class="text-red-600">❌</span>';
                })
                ->html(),
            Column::make("En Catalogo", "mostrar_feed")
                ->sortable()
                ->collapseOnMobile()
                ->format(function ($row) {
                    return $row
                        ? '<span class="text-green-600">✅</span>'
                        : '<span class="text-red-600">❌</span>';
                })
                ->html(),
            Column::make("Acciones")
                ->label(
                    fn($row, Column $column) => view('livewire.articulo.actions', ['row' => $row])
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
