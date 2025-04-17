<?php

namespace App\Http\Livewire\Ingreso;

use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Venta;


class DeudoresTable extends DataTableComponent
{
    public $mostrarPagos;
    protected $model = Venta::class;
    protected $listeners = ['refreshTablePersonas' => '$refresh', 'pagoGuardado' => 'handlePagoGuardado'];

    public function builder(): Builder
    {
        $idLocal = auth()->user()->local->id;

        return Venta::with('persona') // cargamos relación con cliente
            ->where('saldo', '>', 0)
            ->where('tipo_venta', 'cuenta_corriente')
            ->where('ventas.id_local', $idLocal)
            ->orderBy('ventas.created_at', 'asc'); // más antigua primero
    }

    public function configure(): void
    {
        $this->setPrimaryKey('id');
        $this->setSearchEnabled();
    }

    public function columns(): array
    {
        return [
            Column::make("ID", "id")
                ->sortable(),

            Column::make("Cliente", "persona.nombre")
                ->searchable()
                ->sortable(),
            Column::make("idpersona", "persona.idpersona")
                ->searchable()
                ->sortable()
                ->hideIf(true),

            Column::make("Total", "total_venta")
                ->sortable(),

            Column::make("Pago inicial", "pago")
                ->sortable(),

            Column::make("Saldo pendiente", "saldo")
                ->sortable(),

            Column::make("Fecha", "created_at")
                ->sortable()
                ->format(function ($value, $column, $row) {
                    $fecha = \Carbon\Carbon::parse($value);
                    $vencida = $fecha->diffInDays(now()) > 30;

                    $color = $vencida ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800';
                    $texto = $fecha->format('d/m/Y');

                    return "<span class='inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium {$color}'>{$texto}</span>";
                })
                ->html(), // 🔥 IMPORTANTE: esto permite renderizar HTML en la celda
            Column::make("Acciones")->label(
                fn($row, Column $column) => view('livewire.ingreso.cargarpago', ['row' => $row])
            ),

        ];
    }
    public function mostrarPagos($idpersona)
    {
        $this->emit('verPagos', $idpersona);  // Emite el evento 'verPagos' con el idpersona
    }

    public function handlePagoGuardado()
    {
        $this->emit('NoVerPagos');
    }
}
