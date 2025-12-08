<?php

namespace App\Http\Livewire;

use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use App\Models\Pedido;
use Illuminate\Database\Eloquent\Builder;



class PedidoTable extends DataTableComponent
{
    protected $model = Pedido::class;
    public string $pedido_type;


    protected $listeners = ['refreshDatatablePedidos' => '$refresh'];

    public function builder(): Builder
    {
        $idLocal = auth()->user()->local->id;
        $this->pedido_type = auth()->user()->local->tipo;

        return Pedido::where('pedidos.id_local', $idLocal)
            ->with('detalles.producto')
            ->select('pedidos.*')
            ->orderByDesc('pedidos.id');
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
        $this->setTheme('tailwind');
    }

    public function columns(): array
    {
        // Conjunto de columnas comunes para ambos tipos
        $commonColumns = [
            Column::make("ID", "id")
                ->sortable()
                ->collapseOnMobile(),
            Column::make("Cliente", "nombre_cliente")
                ->sortable()
                ->searchable()
                ->format(function ($value, $row) {
                    return "
                        <div class='flex flex-col'>
                            <span class='font-bold text-gray-900'>{$value}</span>
                            <span class='text-xs text-gray-500'>{$row->email}</span>
                        </div>
                    ";
                })
                ->html(),
            Column::make("Email", "email")
                ->sortable()
                ->collapseOnMobile()
                ->deselected(),
            Column::make("Telefono", "telefono")
                ->sortable()
                ->collapseOnMobile()
                ->format(function ($telefono) {
                    if (empty($telefono)) {
                        return '-';
                    }
                    $mensaje = "Hola, me comunico de Tienda Dux para confirmar el pedido realizado en nuestra plataforma";
                    $mensajeCodificado = urlencode($mensaje);
                    $telefonoLimpio = preg_replace('/[^0-9+]/', '', $telefono);
                    $urlWhatsApp = "https://wa.me/{$telefonoLimpio}?text={$mensajeCodificado}";

                    return "<a href='{$urlWhatsApp}' target='_blank' class='text-green-600 hover:text-green-800 hover:underline font-medium'>
                                <i class='fab fa-whatsapp mr-1'></i>{$telefono}
                            </a>";
                })
                ->html(),
            Column::make("Total", "total")
                ->sortable()
                ->format(function ($value) {
                    return "<span class='font-bold text-gray-900'>$ " . number_format($value, 2) . "</span>";
                })
                ->html(),
            Column::make("Pago", "metodo_pago")
                ->sortable()
                ->collapseOnMobile()
                ->format(fn($value) => ucfirst($value)),
        ];

        $productSpecificColumns = [
            Column::make("Direccion", "direccion")
                ->sortable()
                ->collapseOnMobile()
                ->deselected(),
            Column::make("Ciudad", "ciudad")
                ->sortable()
                ->searchable()
                ->collapseOnMobile()
                ->deselected(),
            Column::make("Estado", "estado")
                ->sortable()
                ->format(function ($estado) {
                    $clases = [
                        'pendiente'    => 'bg-yellow-100 text-yellow-800',
                        'confirmado'   => 'bg-blue-100 text-blue-800',
                        'en_proceso'   => 'bg-purple-100 text-purple-800',
                        'entregado'    => 'bg-green-100 text-green-800',
                        'cancelado'    => 'bg-red-100 text-red-800',
                    ];

                    $textos = [
                        'pendiente'    => 'Pendiente',
                        'confirmado'   => 'Confirmado',
                        'en_proceso'   => 'En proceso',
                        'entregado'    => 'Entregado',
                        'cancelado'    => 'Cancelado',
                    ];

                    $clase = $clases[$estado] ?? 'bg-gray-100 text-gray-800';
                    $texto = $textos[$estado] ?? ucfirst($estado);

                    return "<span class='px-2 py-1 rounded-full text-xs font-semibold {$clase}'>$texto</span>";
                })
                ->html(),
        ];

        $serviceSpecificColumns = [
            Column::make("Fecha", "fecha_servicio")
                ->sortable()
                ->collapseOnMobile(),
            Column::make("Hora", "hora_inicio")
                ->sortable()
                ->collapseOnMobile(),
            Column::make("Estado", "estado_reserva")
                ->sortable()
                ->format(function ($value) {
                    $clases = [
                        'pendiente'    => 'bg-yellow-100 text-yellow-800',
                        'confirmado'   => 'bg-blue-100 text-blue-800',
                        'cancelada'    => 'bg-red-100 text-red-800',
                        'completada'   => 'bg-green-100 text-green-800',
                    ];
                    $texto = ucfirst($value);
                    $clase = $clases[$value] ?? 'bg-gray-100 text-gray-800';
                    return "<span class='px-2 py-1 rounded-full text-xs font-semibold {$clase}'>$texto</span>";
                })
                ->html(),
        ];

        $acciones = [
            Column::make("Acciones")
                ->label(
                    fn($row, Column $column) => view('livewire.pedido.actions', ['row' => $row])
                )
                ->html(),
        ];

        if ($this->pedido_type === 'venta') {
            return array_merge($commonColumns, $productSpecificColumns, $acciones);
        } elseif ($this->pedido_type === 'servicio') {
            return array_merge($commonColumns, $serviceSpecificColumns, $acciones);
        }

        return array_merge($commonColumns, $acciones);
    }
}
