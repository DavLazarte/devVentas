<?php

namespace App\Http\Livewire\Caja;

use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use App\Models\CierreCaja;
use Illuminate\Database\Eloquent\Builder;

class CajasTable extends DataTableComponent
{
    protected $model = CierreCaja::class;

    protected $listeners = ['refreshDatatableCajas' => '$refresh'];

    public function builder(): Builder
    {
        $idLocal = auth()->user()->local->id;

        return CierreCaja::where('cierre_cajas.id_local', $idLocal)
            ->orderBy('fecha_apertura', 'desc'); // Ordenar por fecha_apertura descendente
    }


    public function configure(): void
    {
        $this->setPrimaryKey('id');
        $this->setSearchEnabled();
    }
    public function columns(): array
    {
        return [
            Column::make("Id", "id")
                ->sortable(),
            Column::make("Apertura", "fecha_apertura")
                ->sortable()
                ->searchable(),
            Column::make("Cierre", "fecha_cierre")
                ->sortable(),
            Column::make("Total ventas", "monto_total_ventas")
                ->sortable()
                ->searchable(),
            Column::make("Monto apertura", "monto_apertura")
                ->sortable(),
            Column::make("Monto cierre", "monto_cierre")
                ->sortable(),
            Column::make("Monto cierre real", "monto_cierre_real")
                ->sortable(),
            Column::make("Ventas efectivo", "ventas_efectivo")
                ->sortable(),
            Column::make("Ventas transferencia", "ventas_transferencia")
                ->sortable(),
            Column::make("Ventas Cuentas", "ventas_cuenta")
                ->sortable(),
            Column::make("Ventas tarjeta", "ventas_tarjeta")
                ->sortable(),
            Column::make("Ingresos", "ingresos")
                ->sortable(),
            Column::make("Salidas", "salidas")
                ->sortable(),
            Column::make("Estado", "estado")
                ->sortable()
                ->format(function ($value, $column, $row) {
                    $badgeColor = $value === 'abierta' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
                    $badgeText = ucfirst($value); // Capitaliza la primera letra
                    return "<span class='inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium {$badgeColor}'>{$badgeText}</span>";
                })
                ->html(),
            Column::make("Acciones")
                ->label(
                    fn($row, Column $column) => view('livewire.caja.actions', ['row' => $row])
                ),
        ];
    }
    public function editar($id)
    {
        $caja = CierreCaja::findOrFail($id);
        $this->emit('editarCaja', $caja->id);
    }
    public function verCaja($id)
    {
        $caja = CierreCaja::findOrFail($id);
        $this->emit('verCaja', $caja->id);
    }

    public function borrar($id)
    {
        CierreCaja::find($id)->delete();
        session()->flash('message', 'Caja eliminada exitosamente.');
    }
}
