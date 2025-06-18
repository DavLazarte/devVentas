<?php

namespace App\Http\Livewire;

use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use App\Models\Pedido;
use Illuminate\Database\Eloquent\Builder;


class PedidoTable extends DataTableComponent
{
    protected $model = Pedido::class;

    protected $listeners = ['refreshDatatablePedidos' => '$refresh'];

    public function builder(): Builder
    {
        $idLocal = auth()->user()->local->id;

        // Construir la consulta de artículos pertenecientes al local del usuario
        $query = Pedido::where('pedidos.id_local', $idLocal);

        // Obtener los artículos con sus categorías
        return $query->with('detalles.producto')->select('pedidos.*');;
    }

    public function verDetalle($id)
    {
        $this->emitTo('pedidos', 'verDetallePedido', $id);
    }

    public function editar($id)
    {
        $this->emitTo('pedidos', 'editarPedido', $id);
    }

    public function eliminar($id)
    {
        $this->emitTo('pedidos', 'eliminarPedido', $id);
    }

    public function configure(): void
    {
        $this->setPrimaryKey('id');
        $this->setSearchEnabled();
    }

    public function columns(): array
    {
        return [

            // Column::make("Id user", "id_user")
            //     ->sortable(),
            Column::make("Nombre cliente", "nombre_cliente")
                ->sortable()
                ->searchable(),
            Column::make("Email", "email")
                ->sortable(),
            Column::make("Telefono", "telefono")
                ->sortable(),
            Column::make("Direccion", "direccion")
                ->sortable(),
            Column::make("Ciudad", "ciudad")
                ->sortable(),
            Column::make("Codigo postal", "codigo_postal")
                ->sortable(),
            Column::make("Notas entrega", "notas_entrega")
                ->sortable(),
            Column::make("Subtotal", "subtotal")
                ->sortable(),
            Column::make("Envio", "envio")
                ->sortable(),
            Column::make("Descuento", "descuento")
                ->sortable(),
            Column::make("Total", "total")
                ->sortable(),
            Column::make("Estado", "estado")
                ->sortable(),
            Column::make("Metodo pago", "metodo_pago")
                ->sortable(),
            Column::make("Crear cuenta", "crear_cuenta")
                ->sortable()
                ->format(function ($row) {
                    return $row
                        ? '<span class="text-green-600">✅</span>'
                        : '<span class="text-red-600">❌</span>';
                })
                ->html(),
            Column::make("Acciones")
                ->label(
                    fn($row, Column $column) => view('livewire.pedido.actions', ['row' => $row])
                ),
        ];
    }
}
