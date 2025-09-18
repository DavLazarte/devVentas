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
    }

    public function columns(): array
    {
        // Conjunto de columnas comunes para ambos tipos
        $commonColumns = [
            // Column::make("Id user", "id_user")
            //     ->sortable(),
            Column::make("Nombre cliente", "nombre_cliente")
                ->sortable()
                ->searchable(),
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

                    // Mensaje predefinido
                    $mensaje = "Hola, me comunico de Tienda Dux para confirmar el pedido realizado en nuestra plataforma";

                    // Codificar el mensaje para URL
                    $mensajeCodificado = urlencode($mensaje);

                    // Limpiar el número de teléfono (remover espacios, guiones, etc.)
                    $telefonoLimpio = preg_replace('/[^0-9+]/', '', $telefono);

                    // Crear el enlace de WhatsApp
                    $urlWhatsApp = "https://wa.me/{$telefonoLimpio}?text={$mensajeCodificado}";

                    return "<a href='{$urlWhatsApp}' target='_blank' class='text-green-600 hover:text-green-800 hover:underline font-medium'>
                                <i class='fab fa-whatsapp mr-1'></i>{$telefono}
                            </a>";
                })
                ->html(),
            Column::make("Total", "total")
                ->sortable(),
            Column::make("Metodo pago", "metodo_pago")
                ->sortable()
                ->collapseOnMobile()
                ->deselected(),
            // Column::make("Crear cuenta", "crear_cuenta")
            //     ->sortable()
            //     ->format(function ($row) {
            //         return $row
            //             ? '<span class="text-green-600">✅</span>'
            //             : '<span class="text-red-600">❌</span>';
            //     })
            //     ->html(),
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
            Column::make("Codigo postal", "codigo_postal")
                ->sortable()
                ->collapseOnMobile()
                ->deselected(),
            Column::make("Notas entrega", "notas_entrega")
                ->sortable()
                ->collapseOnMobile()
                ->deselected(),
            Column::make("Subtotal", "subtotal")
                ->sortable(),
            Column::make("Envio", "envio")
                ->sortable()
                ->collapseOnMobile()
                ->deselected(),
            Column::make("Descuento", "descuento")
                ->sortable()
                ->collapseOnMobile()
                ->deselected(),
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
        ];
        $serviceSpecificColumns = [
            Column::make("Fecha", "fecha_servicio")
                ->sortable()
                ->collapseOnMobile(),
            Column::make("Hora Inicio", "hora_inicio")
                ->sortable()
                ->collapseOnMobile(),
            Column::make("Estado de Reserva", "estado_reserva")
                ->sortable()
                ->format(function ($value) {
                    $clases = [
                        'pendiente'    => 'bg-gray-400 text-white',
                        'confirmado'   => 'bg-blue-500 text-white',
                        'cancelada'    => 'bg-red-500 text-white',
                        'completada'   => 'bg-green-500 text-white',
                    ];
                    $texto = ucfirst($value);
                    $clase = $clases[$value] ?? 'bg-gray-500 text-white';
                    return "<span class='px-3 py-1 rounded-full text-xs font-semibold {$clase}'>$texto</span>";
                })
                ->html(),

        ];
        $acciones = [
            Column::make("Acciones")
                ->label(
                    fn($row, Column $column) => view('livewire.pedido.actions', ['row' => $row])
                )
                ->html()
                ->collapseOnMobile(),
        ];
        // Lógica para determinar qué columnas mostrar
        if ($this->pedido_type === 'venta') {
            return array_merge($commonColumns, $productSpecificColumns, $acciones);
        } elseif ($this->pedido_type === 'servicio') {
            return array_merge($commonColumns, $serviceSpecificColumns, $acciones);
        }

        return array_merge(
            [
                Column::make("ID", "id")->sortable(),
                Column::make("Tipo", "tipo_pedido")->sortable()->format(fn($value) => ucfirst($value)),
                Column::make("Cliente", "nombre_cliente")->sortable()->searchable(),
                Column::make("Estado", "estado_reserva")->sortable(), // Cambiado a estado_reserva para incluir ambos
                Column::make("Total", "total")->sortable(),
            ],
            $acciones
        );
    }
}
