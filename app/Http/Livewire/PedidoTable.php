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
                ->sortable()
                ->collapseOnMobile(),
            Column::make("Telefono", "telefono")
                ->sortable()
                ->collapseOnMobile(),
            Column::make("Direccion", "direccion")
                ->sortable()
                ->collapseOnMobile(),
            Column::make("Ciudad", "ciudad")
                ->sortable()
                ->searchable()
                ->collapseOnMobile(),
            Column::make("Codigo postal", "codigo_postal")
                ->sortable()
                ->collapseOnMobile(),
            Column::make("Notas entrega", "notas_entrega")
                ->sortable()
                ->collapseOnMobile(),
            Column::make("Subtotal", "subtotal")
                ->sortable(),
            Column::make("Envio", "envio")
                ->sortable()
                ->collapseOnMobile(),
            Column::make("Descuento", "descuento")
                ->sortable()
                ->collapseOnMobile(),
            Column::make("Total", "total")
                ->sortable(),
            Column::make("Estado", "estado")
                ->sortable()
                ->format(function ($estado) {
                    $clases = [
                        'pendiente'    => 'bg-black text-white',   // negro
                        'confirmado'   => 'bg-blue-500 text-white',  // azul
                        'en_proceso'   => 'bg-purple-500 text-white',  // Morado
                        'entregado'    => 'bg-green-500 text-white',   // Verde
                        'cancelado'    => 'bg-red-500 text-white',     // Rojo
                    ];

                    $textos = [
                        'pendiente'    => 'Pendiente',
                        'confirmado'   => 'Confirmado',
                        'en_proceso'   => 'En proceso',
                        'entregado'    => 'Entregado',
                        'cancelado'    => 'Cancelado',
                    ];

                    $clase = $clases[$estado] ?? 'bg-gray-500 text-white';
                    $texto = $textos[$estado] ?? ucfirst($estado);

                    return "<span class='px-3 py-1 rounded-full text-xs font-semibold {$clase}'>$texto</span>";
                })
                ->html(),

            Column::make("Metodo pago", "metodo_pago")
                ->sortable()
                ->collapseOnMobile(),
            // Column::make("Crear cuenta", "crear_cuenta")
            //     ->sortable()
            //     ->format(function ($row) {
            //         return $row
            //             ? '<span class="text-green-600">✅</span>'
            //             : '<span class="text-red-600">❌</span>';
            //     })
            //     ->html(),
            Column::make("Acciones")
                ->label(
                    fn($row, Column $column) => view('livewire.pedido.actions', ['row' => $row])
                ),
        ];
    }
}
