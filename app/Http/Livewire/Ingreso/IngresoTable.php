<?php

namespace App\Http\Livewire\Ingreso;

use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use App\Models\Ingreso;
use Illuminate\Database\Eloquent\Builder;

class IngresoTable extends DataTableComponent
{
    protected $model = Ingreso::class;
    protected $listeners = ['refreshDatatableIngresos' => '$refresh'];

    public function builder(): Builder
    {
        $idLocal = auth()->user()->local->id;

        $query = Ingreso::where('ingresos.id_local', $idLocal);

        // Obtener los artículos con sus categorías
        return $query->with('cliente');
    }

    public function configure(): void
    {
        $this->setPrimaryKey('id_ingreso');
        $this->setSearchEnabled();
    }

    public function columns(): array
    {
        return [
            Column::make("Id", "id_ingreso")
                ->sortable(),
            Column::make("Cliente", "cliente.nombre")
                ->sortable()
                ->searchable(),
            Column::make("Monto", "monto")
                ->sortable(),
            Column::make("Descripcion", "descripcion")
                ->sortable(),
            Column::make("Saldo", "saldo")
                ->sortable(),
            Column::make("Acciones")
                ->label(
                    fn ($row, Column $column) => view('livewire.ingreso.actions', ['row' => $row])
                ),
        ];
    }

    public function borrar($id)
    {
        Ingreso::find($id)->delete();
        session()->flash('message', 'Pago eliminado exitosamente.');
    }
}
