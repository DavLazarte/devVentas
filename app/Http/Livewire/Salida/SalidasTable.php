<?php

namespace App\Http\Livewire\Salida;

use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use App\Models\Salida;
use Illuminate\Database\Eloquent\Builder;


class SalidasTable extends DataTableComponent
{
    protected $model = Salida::class;

    protected $listeners = ['refreshDatatableSalidas' => '$refresh'];

    public function builder(): Builder
    {
        $idLocal = auth()->user()->local->id;

        $query = Salida::where('salidas.id_local', $idLocal);

        // Obtener los artículos con sus categorías
        return $query->with('persona');
    }

    public function configure(): void
    {
        $this->setPrimaryKey('idsalida');
        $this->setSearchEnabled();
    }

    public function columns(): array
    {
        return [
            Column::make("Id", "idsalida")
                ->sortable(),
            Column::make("Proveedor", "persona.nombre")
                ->sortable()
                ->searchable(),
            Column::make("Tipo salida", "tipo_salida")
                ->sortable()
                ->searchable(),
            Column::make("Monto", "monto")
                ->sortable(),
            Column::make("Descripcion", "descripcion")
                ->sortable(),
            Column::make("Saldo", "saldo")
                ->sortable(),
            Column::make("Acciones")->label(
                    fn($row, Column $column) => view('livewire.salida.actions', ['row' => $row])
                ),
        ];
    }



    public function borrar($id)
    {
        Salida::find($id)->delete();
        session()->flash('message', 'Salida eliminada exitosamente.');
    }
}
