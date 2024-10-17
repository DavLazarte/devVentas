<?php

namespace App\Http\Livewire\List;

use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use App\Models\Venta;
use Illuminate\Database\Eloquent\Builder;

class ListVentasTable extends DataTableComponent
{
    public $fecha;
    protected $model = Venta::class;
    protected $listeners = ['refreshDatatableVantas' => '$refresh'];

    public function mount($fecha = null)
    {
        $this->fecha = $fecha; // Asignar la fecha si se pasa
    }

    public function builder(): Builder
    {
        $idLocal = auth()->user()->local->id;

        $query = Venta::where('ventas.id_local', $idLocal);

        // Si se proporciona la fecha, filtrar por la fecha de creación
        if ($this->fecha) {
            $query->whereDate('ventas.created_at', $this->fecha);
        }

        // Ordenar por la fecha de creación (más reciente primero)
        $query->orderBy('ventas.created_at', 'desc');


        // Obtener los artículos con sus categorías
        return $query->with('persona');
    }
    public function configure(): void
    {
        $this->setPrimaryKey('id');
        $this->setSearchEnabled();
    }

    public function columns(): array
    {
        return [
            Column::make("Id Venta", "id")
                ->sortable(),
            Column::make("Cliente", "persona.nombre")
                ->sortable()
                ->searchable()
                ->format(function ($value, $row) {
                    return $value ? $value : 'Consumidor Final';
                }),
            Column::make("Tipo venta", "tipo_venta")
                ->sortable(),
            Column::make("Total venta", "total_venta")
                ->sortable(),
            Column::make("Pago", "pago")
                ->sortable(),
            Column::make("Forma de pago", "forma_de_pago")
                ->sortable(),
            Column::make("Saldo", "saldo")
                ->sortable(),
            Column::make("Estado", "estado")
                ->sortable(),
            Column::make("Acciones")
                ->label(
                    fn($row, Column $column) => view('livewire.list.actionsventas', ['row' => $row])
                ),

        ];
    }
}
