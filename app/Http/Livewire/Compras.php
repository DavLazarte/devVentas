<?php

namespace App\Http\Livewire;

use Livewire\Component;

use App\Models\Articulo;
use App\Models\ArticuloVariante;
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
    public $variantes_disponibles = [];
    public $variante_seleccionada = null;
    public $mostrar_variantes = false;
    public $descuento = 0;
    public $recargo = 0;
    public $compra_total_original;

    protected $listeners = [
        'articuloGuardado' => 'refrescarArticulos'
    ];

    public function mount()
    {
        $this->idLocal = auth()->user()->local->id;
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

    public function refrescarArticulos()
    {
        $this->filtrarArticulo();
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
        $query = Articulo::where('id_local', $this->idLocal);

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

        $this->articulo = $query->select('idarticulo', 'nombre', 'descripcion', 'tiene_variantes')
            ->with('variantesActivas:id_variante,idarticulo,descripcion_variante,stock,precio_unitario,sku')
            ->get();
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
    public function eliminarArticulo($index)
    {
        if (isset($this->articuloSeleccionado[$index])) {
            unset($this->articuloSeleccionado[$index]);
            $this->actualizarTotal();
        }
    }
    public function calcularSubTotalProducto($index)
    {
        $articulo = $this->articuloSeleccionado[$index] ?? null;

        if ($articulo) {
            // Mantén una referencia del stock original
            $stock_original = $articulo['stock_original'] ?? $articulo['stock'];

            // Obtenemos los valores necesarios para el cálculo
            $cantidad = $articulo['cantidad'] ?? 0;
            $precio_compra = $articulo['precio_compra'] ?? 0;
            $porcentaje_ganancia = $articulo['porcentaje_ganancia'] ?? 0;

            // Calcular subtotal
            $calc_subtotal = $cantidad * $precio_compra;
            $subtotal = round($calc_subtotal, 2);

            // Calcular precio de venta basado en el porcentaje de ganancia
            $precio_venta = $precio_compra + ($precio_compra * $porcentaje_ganancia / 100);
            $precio_venta = round($precio_venta, 2);

            // Aumentamos el stock basado en el stock original y la cantidad comprada
            $nuevo_stock = $stock_original + $cantidad;

            // Actualizamos el valor en el arreglo de artículos seleccionados
            $this->articuloSeleccionado[$index]['subtotal'] = $subtotal;
            $this->articuloSeleccionado[$index]['precio_venta'] = $precio_venta;
            $this->articuloSeleccionado[$index]['stock'] = $nuevo_stock;
            $this->articuloSeleccionado[$index]['stock_original'] = $stock_original;

            // Actualizamos el total
            $this->actualizarTotal();
        }
    }

    public function actualizarTotal()
    {
        $subTotal = collect($this->articuloSeleccionado)
            ->sum(function ($art) {
                return $art['subtotal'] ?? 0;
            });

        $subTotal = round($subTotal, 2);
        $this->compra_total = $subTotal;
        $this->pago = $subTotal;
    }
    public function calcularNuevoTotal()
    {
        $this->actualizarTotal();

        $this->compra_total_original = $this->compra_total;

        // Limitar descuento y recargo al rango válido (0-100%)
        $this->descuento = max(0, min(100, $this->descuento));
        $this->recargo = max(0, min(100, $this->recargo));

        // Calcular descuento y aplicar recargo
        $descuento_monto = ($this->compra_total_original * $this->descuento) / 100;
        $recarga_monto = ($this->compra_total_original * $this->recargo) / 100;
        $this->compra_total = round($this->compra_total_original - $descuento_monto + $recarga_monto, 2);

        // Ajustar el pago automáticamente al nuevo total
        $this->pago = $this->compra_total;
    }
    public function calcularSaldo()
    {
        $pago = is_numeric($this->pago) ? floatval($this->pago) : 0;
        $compra = is_numeric($this->compra_total) ? floatval($this->compra_total) : 0;
        $this->saldo = round($compra - $pago, 2);
    }

    public function incrementarCantidad($index)
    {
        if (isset($this->articuloSeleccionado[$index])) {
            $this->articuloSeleccionado[$index]['cantidad']++;
            $this->calcularSubTotalProducto($index);
        }
    }

    public function decrementarCantidad($index)
    {
        if (isset($this->articuloSeleccionado[$index]) && $this->articuloSeleccionado[$index]['cantidad'] > 1) {
            $this->articuloSeleccionado[$index]['cantidad']--;
            $this->calcularSubTotalProducto($index);
        }
    }
    public function guardar()
    {
        // Validar que haya artículos seleccionados
        if (empty($this->articuloSeleccionado)) {
            session()->flash('error', 'Debe agregar al menos un producto a la compra.');
            return;
        }
    
        // Validar que todos los productos tengan precio de compra
        foreach ($this->articuloSeleccionado as $index => $articulo) {
            if (empty($articulo['precio_compra']) || $articulo['precio_compra'] <= 0) {
                session()->flash('error', 'Todos los productos deben tener un precio de compra válido.');
                return;
            }
        }
    
        // Validar compra a cuenta corriente
        if ($this->tipo_venta === 'cuenta_corriente') {
            if (!$this->proveedorSeleccionado) {
                session()->flash('error', 'Debe seleccionar un proveedor para compras a cuenta corriente.');
                return;
            }
        }
    
        // Validar que el pago no sea mayor al total en compra rápida
        if ($this->tipo_venta === 'venta_rapida' && $this->saldo > 0) {
            session()->flash('error', 'Una compra rápida no puede tener saldo pendiente. Ajuste el pago o cambie a cuenta corriente.');
            return;
        }
    
        try {
            DB::beginTransaction();
            
            // Ajustar el valor de saldo
            $this->saldo = max(0, $this->saldo);
    
            $compra = Compra::updateOrCreate(
                ['id' => $this->id_compra],
                [
                    'idpersona' => $this->idproveedor,
                    'tipo_compra' => $this->tipo_venta,
                    'num_recibo' => !empty($this->num_recibo) ? $this->num_recibo : null, // Permitir nulo
                    'total' => $this->compra_total,
                    'descuento' => $this->descuento,
                    'recargo' => $this->recargo,
                    'total_original' => $this->compra_total_original,
                    'pago' => $this->pago,
                    'tipo_pago' => $this->forma_de_pago,
                    'saldo' => $this->saldo,
                    'id_local' => $this->idLocal,
                ]
            );
    
            foreach ($this->articuloSeleccionado as $articulo) {
                // Crear detalle de compra con soporte para variantes
                $detalle_compra = Detalle_compra::create([
                    'id_compra' => $compra->id,
                    'idarticulo' => $articulo['idarticulo'],
                    'id_variante' => $articulo['id_variante'] ?? null,
                    'sku_comprado' => $articulo['sku'],
                    'descripcion_variante' => $articulo['descripcion_variante'] ?? null,
                    'cantidad' => $articulo['cantidad'],
                    'precio_compra' => $articulo['precio_compra'],
                ]);
    
                // Actualizar stock Y precio de venta según si es variante o producto simple
                if (isset($articulo['id_variante']) && $articulo['id_variante']) {
                    // Actualizar stock y precio de la variante
                    $variante = ArticuloVariante::find($articulo['id_variante']);
                    if ($variante) {
                        $variante->stock = $articulo['stock'];
                        $variante->precio_unitario = $articulo['precio_venta'];
                        $variante->save();
                    }
                } else {
                    // Actualizar stock y precio del producto principal
                    $articuloModel = Articulo::find($articulo['idarticulo']);
                    if ($articuloModel) {
                        $articuloModel->stock = $articulo['stock'];
                        $articuloModel->precio_unitario = $articulo['precio_venta'];
                        $articuloModel->save();
                    }
                }
            }
    
            DB::commit();
    
            $this->mensajeVenta = 'COMPRA EXITOSA!';
    
            $this->reset([
                'nombre_cliente',
                'compra_total',
                'descuento',
                'recargo',
                'compra_total_original',
                'persona',
                'articulo',
                'id_articulo',
                'precio_compra',
                'cantidad',
                'subtotal',
                'saldo',
                'pago',
                'id_compra',
                'articuloSeleccionado',
                'proveedorSeleccionado',
                'nombre_cliente',
                'idproveedor',
                'searchCliente',
                'searchArticulo',
                'num_recibo'
            ]);
    
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Ocurrió un error al guardar la compra: ' . $e->getMessage());
            Log::error($e);
            session()->flash('error', 'Error al guardar la compra. Por favor, intente nuevamente.');
            return back();
        }
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

        $this->articuloSeleccionado[] = [
            'idarticulo' => $articulo->idarticulo,
            'id_variante' => $variante?->id_variante,
            'nombre' => $variante ?
                $articulo->nombre . ' - ' . $variante->descripcion_variante :
                $articulo->nombre,
            'sku' => $variante?->sku ?? $articulo->codigo,
            'precio_compra' => 0,
            'porcentaje_ganancia' => 0,    
            'precio_venta' => 0,            
            'stock' => $variante?->stock ?? $articulo->stock,
            'stock_original' => $variante?->stock ?? $articulo->stock,
            'cantidad' => 1,
            'descripcion' => $articulo->descripcion,
            'descripcion_variante' => $variante?->descripcion_variante,
            'subtotal' => 0
        ];

        $this->calcularSubTotalProducto(count($this->articuloSeleccionado) - 1);
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
}
