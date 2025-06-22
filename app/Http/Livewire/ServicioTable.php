<?php

namespace App\Http\Livewire;

use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use App\Models\Servicio;
use Illuminate\Database\Eloquent\Builder;


class ServicioTable extends DataTableComponent
{
    protected $model = Servicio::class;
    protected $listeners = ['refreshDatatableServicios' => '$refresh'];

    public function builder(): Builder
    {
        $idLocal = auth()->user()->local->id;

        // Construir la consulta de artículos pertenecientes al local del usuario
        $query = Servicio::where('servicios.id_local', $idLocal);

        // Obtener los artículos con sus categorías
        return $query->with('categoria')->select('servicios.*');;
    }


    public function configure(): void
    {
        $this->setPrimaryKey('idservicio');
        $this->setSearchEnabled(); // Habilitar la búsqueda
    }

    public function columns(): array
    {
        return [

            Column::make("Categoría", "categoria.nombre")
                ->sortable()
                ->searchable()
                ->collapseOnMobile(),
            Column::make("Nombre", "nombre")
                ->sortable()
                ->searchable(),
            Column::make("Descripcion", "descripcion")
                ->sortable()
                ->collapseOnMobile(),
            Column::make("Precio", "precio")
                ->sortable(),
            Column::make("Duracion", "duracion")
                ->sortable()
                ->collapseOnMobile(),
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
            Column::make("En catalogo", "mostrar_feed")
                ->sortable()
                ->collapseOnMobile()
                ->format(function ($row) {
                    return $row
                        ? '<span class="text-green-600">✅</span>'
                        : '<span class="text-red-600">❌</span>';
                })
                ->html(),
            Column::make("Imagen", "imagen")
                ->label(fn($row) => view('livewire.servicio.imagen', ['imagen' => $row->imagen_url])),
            Column::make("Acciones")
                ->label(
                    fn($row, Column $column) => view('livewire.servicio.actions', ['row' => $row])
                ),
        ];
    }
    public function editar($id)
    {
        $servicio = Servicio::findOrFail($id);
        $this->emit('editarServicio', $servicio->idservicio);
    }

    public function borrar($id)
    {
        Servicio::find($id)->delete();
        session()->flash('message', 'Servicio eliminado exitosamente.');
    }
}
