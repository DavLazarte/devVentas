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
    public $persona, $articulo, $id_articulo, $precio_unitario, $cantidad, $subtotal, $saldo, $pago, $id_venta,  $venta_total, $mensajeVenta;
    public $clienteSeleccionado;
    public $articuloSeleccionado = [];
    public $searchCliente = '';
    public $searchArticulo = '';
    public $nombre_cliente = 'consumidor_final';
    public $idcliente;
    public $tipo_venta = "venta_rapida";
    public $forma_de_pago = "efectivo";
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
        $this->articulo = Articulo::where('id_local', $this->idLocal);

        if ($this->searchArticulo) {
            $this->articulo->where('nombre', 'like', '%' . $this->searchArticulo . '%')
                ->orWhere('codigo', 'like', '%' . $this->searchArticulo . '%');;
        }

        $this->articulo = $this->articulo->get();
    }
    public function agregarArticulo($id)
    {
        $articuloSe = Articulo::find($id);

        $this->articuloSeleccionado[] = [
            'idarticulo' => $articuloSe->idarticulo,
            'nombre' => $articuloSe->nombre,
            'precio_unitario' => number_format($articuloSe->precio_unitario, 2, '.', ''),
            'stock' => $articuloSe->stock,
            // Agregar otros campos según tu estructura
        ];
        $this->searchArticulo = '';
    }
    public function eliminarArticulo($index)
    {
        if (isset($this->articuloSeleccionado[$index])) {
            unset($this->articuloSeleccionado[$index]);
            $this->actualizarTotal();
        }
    }
    public function calcularSubTotalProducto($index)
    {
        // Obtenemos los valores necesarios para el cálculo
        $cantidad = $this->articuloSeleccionado[$index]['cantidad'] ?? 0;
        $precio = $this->articuloSeleccionado[$index]['precio_unitario'] ?? 0;
        // $peso = $this->articuloSeleccionado[$index]['peso'] ?? 0;
        $stock_rec = $this->articuloSeleccionado[$index]['stock'] ?? 0;

        // Realizamos el cálculo
        $calc_subtotal = $cantidad * $precio;
        $subtotal = round($calc_subtotal, 2);
        //descontamos stock
        $nuevo_stock = $stock_rec - $cantidad;

        // Actualizamos el valor en el arreglo de materia prima seleccionada
        $this->articuloSeleccionado[$index]['subtotal'] = $subtotal;
        $this->articuloSeleccionado[$index]['stock'] = $nuevo_stock;
        $this->actualizarTotal();
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
    }
    public function calcularSaldo()
    {
        $calc_new_saldo = $this->venta_total - $this->pago;
        $new_saldo = round($calc_new_saldo, 2);
        $this->saldo = $new_saldo;
    }
    public function guardar()
    {
        try {
            // dd($this->idcliente);
            // Validar datos aquí si es necesario

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
                    'pago' => $this->pago,
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
                'nombre_cliente', 'venta_total', 'persona', 'articulo', 'id_articulo', 'precio_unitario',
                'cantidad', 'subtotal', 'saldo', 'pago', 'id_venta',
                'articuloSeleccionado', 'clienteSeleccionado', 'nombre_cliente', 'idcliente',
                'searchCliente', 'searchArticulo', 'tipo_venta','idLocal'
            ]);





            // Redirección a otra página, etc.

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
