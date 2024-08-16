<?php

namespace App\Http\Livewire;

use Livewire\Component;

use App\Models\Articulo;
use App\Models\Detalle_compra;
use App\Models\Persona;
use App\Models\Compra;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Compras extends Component
{
    public $persona, $articulo, $id_articulo, $precio_compra, $cantidad, $subtotal, $saldo, $pago, $id_compra,  $compra_total, $mensajeVenta, $num_recibo;
    public $proveedorSeleccionado;
    public $articuloSeleccionado = [];
    public $searchCliente = '';
    public $searchArticulo = '';
    public $nombre_cliente = 'consumidor_final';
    public $idproveedor;
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
        $this->filtrarProveedor();


        return view('livewire.compras.compras', [
            'persona' => $this->persona,
            'articulo' => $this->articulo,
        ]);
    }

    public function filtrarProveedor()
    {
        $query = Persona::where('id_local', $this->idLocal);
        // $this->persona->where('tipo_persona', 'proveedor');

        if (!empty($this->searchCliente)) {
            $query->where(function ($q) {
                $q->where('idpersona', 'like', '%' . $this->searchCliente . '%')
                    ->orWhere('nombre', 'like', '%' . $this->searchCliente . '%');
            });
        }

        $this->persona = $query->get();
    }
    public function agregarProveedor($id)
    {
        $proveedorSe = Persona::where('id_local', $this->idLocal)
            ->where('tipo_persona', 'proveedor')
            ->find($id);

        if ($proveedorSe) {
            $this->proveedorSeleccionado = $proveedorSe;

            $this->idproveedor = $proveedorSe->idpersona;
            $this->nombre_cliente = $proveedorSe->nombre;
            $this->searchCliente = '';
        } else {
            // Opcional: Agregar algún manejo si no se encuentra el cliente, como un mensaje de error.
            session()->flash('error', 'Proveedor no encontrado en su local.');
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
            // 'precio_unitario' => number_format($articuloSe->precio_unitario, 2, '.', ''),
            'precio_compra' => $this->precio_compra,
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
        $precio = $this->articuloSeleccionado[$index]['precio_compra'] ?? 0;
        $stock_rec = $this->articuloSeleccionado[$index]['stock'] ?? 0;

        // Realizamos el cálculo
        $calc_subtotal = $cantidad * $precio;
        $subtotal = round($calc_subtotal, 2);
        //descontamos stock
        $nuevo_stock = $stock_rec + $cantidad;

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
        $this->compra_total = $subTotal;
        $this->pago = $subTotal;
    }
    public function calcularSaldo()
    {
        $calc_new_saldo = $this->compra_total - $this->pago;
        $new_saldo = round($calc_new_saldo, 2);
        $this->saldo = $new_saldo;
    }
    public function guardar()
    {
        try {
            // dd($this->idproveedor);
            // Validar datos aquí si es necesario

            // Iniciar una transacción para asegurar que todas las operaciones se completen correctamente o se reviertan si hay un error
            DB::beginTransaction();
            // Ajustar el valor de saldo
            $this->saldo = max(0, $this->saldo);

            $compra = Compra::updateOrCreate(
                ['id' => $this->id_compra],
                [
                    'idpersona' => $this->idproveedor,
                    'tipo_compra' => $this->tipo_venta,
                    'num_recibo' => $this->num_recibo,
                    'total' => $this->compra_total,
                    'pago' => $this->pago,
                    'tipo_pago' => $this->forma_de_pago,
                    'saldo' => $this->saldo,
                    'id_local' => $this->idLocal,
                ]
            );

            foreach ($this->articuloSeleccionado as $articulo) {
                // Crear o actualizar el DetalleVenta para cada artículo
                $detalle_compra = Detalle_compra::updateOrCreate(
                    ['id_compra' => $compra->id, 'idarticulo' => $articulo['idarticulo']],
                    [
                        'cantidad' => $articulo['cantidad'],
                        'precio_compra' => $articulo['precio_compra'],
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

            $this->mensajeVenta = 'COMPRA EXITOSA!';


            $this->reset([
                'nombre_cliente', 'compra_total', 'persona', 'articulo', 'id_articulo', 'precio_compra',
                'cantidad', 'subtotal', 'saldo', 'pago', 'id_compra',
                'articuloSeleccionado', 'proveedorSeleccionado', 'nombre_cliente', 'idproveedor',
                'searchCliente', 'searchArticulo', 'num_recibo'
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
