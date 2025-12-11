<?php

namespace App\Http\Livewire\Venta;

use App\Http\Livewire\Persona\PersonaLivewire;
use App\Models\Articulo;
use App\Models\ArticuloVariante;
use App\Models\DetalleVenta;
use App\Models\Persona;
use App\Models\Venta;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;



class Ventas extends Component
{
    protected $listeners = [
        'articuloGuardado' => 'refrescarCatalogoArticulos',
    ];
    public $persona, $articulo, $id_articulo, $descuento, $recargo, $precio_unitario, $cantidad, $subtotal, $saldo, $pago, $id_venta,  $venta_total = 0, $mensajeVenta;
    public $clienteSeleccionado;
    public $articuloSeleccionado = [];
    public $searchCliente = '';
    public $searchArticulo = '';
    public $nombre_cliente = 'consumidor_final';
    public $idcliente;
    public $tipo_venta = "venta_rapida";
    public $forma_de_pago = "";
    // public $venta_total;
    public $venta_total_original;
    public $idLocal;
    public $variantes_disponibles = [];
    public $variante_seleccionada = null;
    public $mostrar_variantes = false;

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

    public function refrescarCatalogoArticulos()
    {
        $this->filtrarArticulo();
    }

    public function updatedSearchArticulo()
    {
        $this->filtrarArticulo();
    }

    public function updatedTipoVenta($value)
    {
        if ($value === 'cuenta_corriente') {
            $this->forma_de_pago = 'cuenta_corriente';
        }
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

    // Método privado que retorna un query base para clientes del local
    private function clienteQuery()
    {
        return Persona::where('id_local', $this->idLocal)
            ->where('tipo_persona', 'cliente');
    }


    public function filtrarCliente()
    {
        $query = $this->clienteQuery();

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
        $clienteSe = $this->clienteQuery()->find($id);

        if ($clienteSe) {
            $this->clienteSeleccionado = $clienteSe;
            $this->idcliente = $clienteSe->idpersona;
            $this->nombre_cliente = $clienteSe->nombre;
            $this->searchCliente = '';
        } else {
            session()->flash('error', 'Cliente no encontrado en su local.');
        }
    }

    public function filtrarArticulo()
    {
        $query = Articulo::where('id_local', $this->idLocal)->where('estado', 'activo');

        if ($this->searchArticulo) {
            $query->where(function ($q) {
                $q->where('nombre', 'like', '%' . $this->searchArticulo . '%')
                    ->orWhere('codigo', 'like', '%' . $this->searchArticulo . '%')
                    ->orWhereHas('variantes', function ($subQ) {
                        $subQ->where('sku', 'like', '%' . $this->searchArticulo . '%')
                            ->where('estado', 'activo');
                    });
            });
        }

        // Carga solo las columnas necesarias para el dropdown
        $this->articulo = $query->select('idarticulo', 'nombre', 'descripcion', 'tiene_variantes')->with('variantesActivas:id_variante,idarticulo,descripcion_variante')->get();
    }
    public function agregarArticulo($id)
    {
        $articuloSe = Articulo::with('variantesActivas')->find($id);

        if (!$articuloSe) {
            session()->flash('error', 'Producto no encontrado');
            return;
        }

        // Si tiene variantes, mostrar selector
        if ($articuloSe->tiene_variantes && $articuloSe->variantesActivas->count() > 0) {
            $this->variantes_disponibles = $articuloSe->variantesActivas;
            $this->mostrar_variantes = true;
            $this->searchArticulo = '';
            return;
        }

        // Producto simple - agregar directamente
        $this->agregarProductoAlCarrito($articuloSe);
        $this->searchArticulo = '';
    }
    public function seleccionarVariante($idVariante)
    {
        $variante = ArticuloVariante::with('articulo')->find($idVariante);

        if (!$variante) {
            session()->flash('error', 'Variante no encontrada');
            return;
        }

        $this->agregarVarianteAlCarrito($variante);
        $this->cerrarSelectorVariantes();
    }

    public function cerrarSelectorVariantes()
    {
        $this->mostrar_variantes = false;
        $this->variantes_disponibles = [];
        $this->variante_seleccionada = null;
    }

    private function agregarProductoAlCarrito($articulo, $variante = null)
    {
        // Verificar si ya está en el carrito
        $index = $this->buscarEnCarrito($articulo->idarticulo, $variante?->id_variante);

        if ($index !== false) {
            // Ya existe, incrementar cantidad
            $this->articuloSeleccionado[$index]['cantidad']++;
            $this->calcularSubTotalProducto($index);
            return;
        }

        // Agregar nuevo item al carrito
        $tipoVenta = $variante?->tipo_venta ?? $articulo->tipo_venta ?? 'unidad';
        $unidadMedida = $variante?->unidad_medida ?? $articulo->unidad_medida;
        $precioBase = $variante?->precio_unitario ?? $articulo->precio_unitario;

        // Si es por peso/volumen, usar precio_por_unidad_medida
        if ($tipoVenta === 'peso' || $tipoVenta === 'volumen') {
            $precioBase = $articulo->precio_por_unidad_medida ?? $precioBase;
        }

        // Determinar el stock correcto según tipo de venta
        $stockDisponible = ($tipoVenta === 'peso' || $tipoVenta === 'volumen')
            ? ($variante?->stock_decimal ?? $articulo->stock_decimal ?? 0)
            : ($variante?->stock ?? $articulo->stock ?? 0);

        $this->articuloSeleccionado[] = [
            'idarticulo' => $articulo->idarticulo,
            'id_variante' => $variante?->id_variante,
            'nombre' => $variante ?
                $articulo->nombre . ' - ' . $variante->descripcion_variante :
                $articulo->nombre,
            'sku' => $variante?->sku ?? $articulo->codigo,
            'precio_unitario' => $precioBase,
            'stock' => $stockDisponible, // Stock correcto según tipo de venta
            'cantidad' => 1,
            'descripcion' => $articulo->descripcion,
            'descripcion_variante' => $variante?->descripcion_variante,
            // Campos para venta por peso/volumen
            'tipo_venta' => $tipoVenta,
            'unidad_medida' => $unidadMedida,
            'precio_por_unidad_medida' => ($tipoVenta === 'peso' || $tipoVenta === 'volumen') ? $precioBase : null,
            'stock_decimal' => ($tipoVenta === 'peso' || $tipoVenta === 'volumen')
                ? $stockDisponible
                : null,
            'permite_decimales' => ($tipoVenta === 'peso' || $tipoVenta === 'volumen'),
        ];

        $this->calcularSubTotalProducto();
    }

    private function agregarVarianteAlCarrito($variante)
    {
        $this->agregarProductoAlCarrito($variante->articulo, $variante);
    }

    private function buscarEnCarrito($idArticulo, $idVariante = null)
    {
        foreach ($this->articuloSeleccionado as $index => $item) {
            if (
                $item['idarticulo'] == $idArticulo &&
                ($item['id_variante'] ?? null) == $idVariante
            ) {
                return $index;
            }
        }
        return false;
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

                // Si es por peso/volumen, también actualizar stock_decimal
                if ($articulo['permite_decimales'] ?? false) {
                    $this->articuloSeleccionado[$index]['stock_decimal'] = $nuevo_stock;
                }

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
        $pago = is_numeric($this->pago) ? floatval($this->pago) : 0;
        $venta = is_numeric($this->venta_total) ? floatval($this->venta_total) : 0;

        $this->saldo = round($venta - $pago, 2);
    }
    public function guardar()
    {
        if (empty($this->forma_de_pago)) {
            $this->dispatchBrowserEvent('errorVenta', ['message' => 'Debe elegir una forma de pago.']);
            return;
        }

        if ($this->tipo_venta === 'venta_rapida' && $this->saldo > 0) {
            $this->dispatchBrowserEvent('errorVenta', ['message' => 'Una venta rápida no puede tener saldo pendiente. Seleccione un cliente o ajuste el pago.']);
            return;
        }
        if ($this->tipo_venta === 'cuenta_corriente' && !$this->clienteSeleccionado) {
            $this->dispatchBrowserEvent('errorVenta', ['message' => 'No Podes Hacer una venta a cuenta sin seleccionar un cliente.']);
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
                // Crear DetalleVenta con soporte para variantes y peso
                $detalle_venta = DetalleVenta::create([
                    'idventa' => $venta->id,
                    'idarticulo' => $articulo['idarticulo'],
                    'id_variante' => $articulo['id_variante'] ?? null,
                    'sku_vendido' => $articulo['sku'],
                    'descripcion_variante' => $articulo['descripcion_variante'] ?? null,
                    'cantidad' => $articulo['cantidad'],
                    'precio_venta' => $articulo['precio_unitario'],
                    'estado' => 'activo',
                    // Campos para venta por peso/volumen
                    'cantidad_decimal' => ($articulo['permite_decimales'] ?? false) ? $articulo['cantidad'] : null,
                    'unidad_medida_venta' => $articulo['unidad_medida'] ?? null,
                ]);

                // Actualizar stock según si es variante o producto simple
                if (isset($articulo['id_variante']) && $articulo['id_variante']) {
                    // Descontar stock de la variante
                    $variante = ArticuloVariante::find($articulo['id_variante']);
                    if ($variante) {
                        // Si es por peso/volumen, actualizar stock_decimal
                        if ($articulo['permite_decimales'] ?? false) {
                            $variante->stock_decimal = $articulo['stock_decimal'];
                        } else {
                            $variante->stock = $articulo['stock'];
                        }
                        $variante->save();
                    }
                } else {
                    // Descontar stock del producto principal
                    $articuloModel = Articulo::find($articulo['idarticulo']);
                    if ($articuloModel) {
                        // Si es por peso/volumen, actualizar stock_decimal
                        if ($articulo['permite_decimales'] ?? false) {
                            $articuloModel->stock_decimal = $articulo['stock_decimal'];
                        } else {
                            $articuloModel->stock = $articulo['stock'];
                        }
                        $articuloModel->save();
                    }
                }
            }
            // dd(session()->all());
            // Confirmar la transacción
            DB::commit();

            $this->mensajeVenta = 'VENTA EXITOSA!';
            $this->tipo_venta = "venta_rapida";
            $this->forma_de_pago = "";

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
                'agregarArticulo',
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
    public function incrementarCantidad($index)
    {
        $this->articuloSeleccionado[$index]['cantidad']++;
        $this->calcularSubTotalProducto($index);
    }

    public function decrementarCantidad($index)
    {
        if ($this->articuloSeleccionado[$index]['cantidad'] > 1) {
            $this->articuloSeleccionado[$index]['cantidad']--;
            $this->calcularSubTotalProducto($index);
        }
    }
}
