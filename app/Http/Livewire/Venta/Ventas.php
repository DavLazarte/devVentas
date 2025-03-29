<?php

namespace App\Http\Livewire\Venta;

use App\Http\Livewire\Persona\PersonaLivewire;
use App\Models\Articulo;
use App\Models\DetalleVenta;
use App\Models\Persona;
use App\Models\Venta;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;



class Ventas extends Component
{
    public $persona, $articulo, $id_articulo, $descuento, $recargo, $precio_unitario, $cantidad, $subtotal, $saldo, $pago, $id_venta,  $venta_total = 0, $mensajeVenta;
    public $clienteSeleccionado;
    public $articuloSeleccionado = [];
    public $searchCliente = '';
    public $searchArticulo = '';
    public $nombre_cliente = 'consumidor_final';
    public $idcliente;
    public $tipo_venta = "venta_rapida";
    public $forma_de_pago = "efectivo";
    // public $venta_total;
    public $venta_total_original;
    public $idLocal;

    public function mount()
    {
        $this->idLocal = auth()->user()->local->id;
    }



    public function render()
    {
        $this->filtrarArticulo();
        $this->filtrarCliente();


        return view('livewire.venta.ventas', [
            'persona' => $this->persona,
            'articulo' => $this->articulo,
        ]);
    }


    public function updated($propertyName)
    {
        if (in_array($propertyName, ['descuento', 'recargo'])) {
            $this->calcularNuevoTotal();
        }

        if ($propertyName === 'pago') {
            $this->calcularSaldo();
        }
    }



    public function filtrarCliente()
    {
        $query = Persona::where('id_local', $this->idLocal);
        // $this->persona->where('tipo_persona', 'cliente');

        if (!empty($this->searchCliente)) {
            $query->where(function ($q) {
                $q->where('idpersona', 'like', '%' . $this->searchCliente . '%')
                    ->orWhere('nombre', 'like', '%' . $this->searchCliente . '%');
            });
        }

        $this->persona = $query->get();
    }
    public function agregarCliente($id)
    {
        // Aquí se busca el cliente por ID solo si corresponde al 'id_local' y al tipo 'cliente'.
        $clienteSe = Persona::where('id_local', $this->idLocal)
            ->where('tipo_persona', 'cliente')
            ->find($id);

        if ($clienteSe) {
            $this->clienteSeleccionado = $clienteSe;
            $this->idcliente = $clienteSe->idpersona;
            $this->nombre_cliente = $clienteSe->nombre;
            $this->searchCliente = '';
        } else {
            // Opcional: Agregar algún manejo si no se encuentra el cliente, como un mensaje de error.
            session()->flash('error', 'Cliente no encontrado en su local.');
        }
    }
    public function filtrarArticulo()
    {
        // Asumiendo que $this->idLocal ya está definido y es el ID del local correcto.
        $query = Articulo::where('id_local', $this->idLocal);

        if ($this->searchArticulo) {
            // Agrupa las condiciones de búsqueda para que actúen juntas
            $query->where(function ($q) {
                $q->where('nombre', 'like', '%' . $this->searchArticulo . '%')
                    ->orWhere('codigo', 'like', '%' . $this->searchArticulo . '%');
            });
        }

        $this->articulo = $query->get();
    }
    public function agregarArticulo($id)
    {
        $articuloSe = Articulo::find($id);

        $this->articuloSeleccionado[] = [
            'idarticulo' => $articuloSe->idarticulo,
            'nombre' => $articuloSe->nombre,
            'precio_unitario' => number_format($articuloSe->precio_unitario, 2, '.', ''),
            'stock' => $articuloSe->stock,
            'cantidad' => 1,
            'descripcion' => $articuloSe->descripcion,
            // Agregar otros campos según tu estructura
        ];
        $this->calcularSubTotalProducto();
        $this->searchArticulo = '';
    }
    public function eliminarArticulo($index)
    {
        if (isset($this->articuloSeleccionado[$index])) {
            unset($this->articuloSeleccionado[$index]);
            $this->actualizarTotal();
        }
    }
    public function calcularSubTotalProducto($index = null)
    {
        if ($index !== null) {

            // Si se proporciona un índice, se calcula el subtotal para el artículo en ese


            // Obtenemos el artículo correspondiente al índice dado
            $articulo = $this->articuloSeleccionado[$index] ?? null;

            if ($articulo) {
                // Mantén una referencia del stock original
                $stock_original = $articulo['stock_original'] ?? $articulo['stock'];
                // Obtenemos los valores necesarios para el cálculo
                $cantidad = $articulo['cantidad'] ?? 0;
                $precio = $articulo['precio_unitario'] ?? 0;
                // $stock_rec = $articulo['stock'] ?? 0;

                // Realizamos el cálculo
                $calc_subtotal = $cantidad * $precio;
                $subtotal = round($calc_subtotal, 2);

                // Descontamos el stock original
                $nuevo_stock = $stock_original - $cantidad;

                // Actualizamos el valor en el arreglo del artículo seleccionado
                $this->articuloSeleccionado[$index]['subtotal'] = $subtotal;
                $this->articuloSeleccionado[$index]['stock'] = $nuevo_stock;
                $this->articuloSeleccionado[$index]['stock_original'] = $stock_original;

                // Actualizamos el total
                $this->actualizarTotal();
            }
        } else {
            // Si no se proporciona un índice, se calcula el subtotal para todos los artículos

            foreach ($this->articuloSeleccionado as $index => $articulo) {
                $this->calcularSubTotalProducto($index);
            }
        }
    }



    public function actualizarTotal()
    {
        $subTotal = collect($this->articuloSeleccionado)
            ->sum(function ($art) {
                return $art['subtotal'] ?? 0;
            });

        // Redondear el subtotal a dos cifras decimales
        $subTotal = round($subTotal, 2);
        $this->venta_total = $subTotal;
        $this->pago = $subTotal;
    }
    public function calcularNuevoTotal()
    {
        $this->actualizarTotal();

        $this->venta_total_original = $this->venta_total;

        // Limitar descuento al rango válido (0-100%)
        $this->descuento = max(0, min(100, $this->descuento));
        $this->recargo = max(0, min(100, $this->recargo));

        // Calcular descuento y aplicar recargo
        $descuento_monto = ($this->venta_total_original * $this->descuento) / 100;
        $recarga_monto = ($this->venta_total_original * $this->recargo) / 100;
        $this->venta_total = round($this->venta_total_original - $descuento_monto + $recarga_monto, 2);

        // Ajustar el pago automáticamente al nuevo total
        $this->pago = $this->venta_total;
    }
    public function calcularSaldo()
    {
        $calc_new_saldo = $this->venta_total - $this->pago;
        $new_saldo = round($calc_new_saldo, 2);
        $this->saldo = $new_saldo;
    }
    public function guardar()
    {
        if ($this->tipo_venta === 'venta_rapida' && $this->saldo > 0) {
            $this->dispatchBrowserEvent('errorVenta', ['message' => 'Una venta rápida no puede tener saldo pendiente. Seleccione un cliente o ajuste el pago.']);
            return;
        }
        
        try {
            // Iniciar una transacción para asegurar que todas las operaciones se completen correctamente o se reviertan si hay un error
            DB::beginTransaction();
            // Ajustar el valor de saldo
            $this->saldo = max(0, $this->saldo);

            $venta = Venta::updateOrCreate(
                ['id' => $this->id_venta],
                [
                    'idcliente' => $this->idcliente,
                    'tipo_venta' => $this->tipo_venta,
                    'total_venta' => $this->venta_total,
                    'descuento' => $this->descuento,
                    'recargo' => $this->recargo,
                    'pago' => $this->pago > $this->venta_total ? $this->venta_total : $this->pago,
                    'forma_de_pago' => $this->forma_de_pago,
                    'saldo' => $this->saldo,
                    'id_local' => $this->idLocal,
                ]
            );

            foreach ($this->articuloSeleccionado as $articulo) {
                // Crear o actualizar el DetalleVenta para cada artículo
                $detalle_venta = DetalleVenta::updateOrCreate(
                    ['idventa' => $venta->id, 'idarticulo' => $articulo['idarticulo']],
                    [
                        'cantidad' => $articulo['cantidad'],
                        'precio_venta' => $articulo['precio_unitario'],
                        // Otros campos según tu estructura de la tabla detalles
                    ]
                );

                // Actualizar el stock del artículo
                $articuloModel = Articulo::find($articulo['idarticulo']);
                if ($articuloModel) {
                    $articuloModel->stock = $articulo['stock'];
                    $articuloModel->save();
                }
            }
            // dd(session()->all());
            // Confirmar la transacción
            DB::commit();

            $this->mensajeVenta = 'VENTA EXITOSA!';

            $this->reset([
                'nombre_cliente',
                'venta_total',
                'descuento',
                'recargo',
                'persona',
                'articulo',
                'id_articulo',
                'precio_unitario',
                'cantidad',
                'subtotal',
                'saldo',
                'pago',
                'id_venta',
                'articuloSeleccionado',
                'clienteSeleccionado',
                'nombre_cliente',
                'idcliente',
                'searchCliente',
                'searchArticulo',
                'tipo_venta',
                'idLocal'
            ]);
        } catch (\Exception $e) {
            // En caso de error, revertir la transacción
            DB::rollback();

            // Manejar el error como desees (mensaje de error, registro en logs, etc.)
            // Registrar el error en el log
            Log::error('Ocurrió un error al guardar: ' . $e->getMessage());
            // Puedes registrar más detalles si lo deseas:
            Log::error($e);

            // Mensaje de error
            session()->flash('error', 'Error al guardar la venta y detalles.');

            // Puedes redirigir a la página anterior o mostrar un mensaje de error en la misma página
            return back();
        }
    }
}
