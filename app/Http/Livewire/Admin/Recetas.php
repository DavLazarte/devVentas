<?php

namespace App\Http\Livewire\Admin;

use App\Models\Receta;
use App\Models\MateriaPrimaModel;
use App\Models\Calculo;
use App\Models\Insumo;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class Recetas extends Component
{
    public $id_materia, $costo_en_receta, $stock_rec, $cantidad, $empleado, $materiaPrima, $nombre, $descripcion, $porciones, $nuevaMateriaPrima, $porcentaje_ganancia, $iva, $costo_elaboracion, $costo_unitario, $precio_unitario, $precio, $precio_iva, $ganancia;
    public $modal = false;
    public $materiaPrimaSeleccionada = [];
    public $subTotalCostoElaboracion;
    public $id_receta, $busqueda;
    public $searchMateriaPrima = '';
    public $selectedMateriaPrima = '';
    // public $materiaPrima;


    use WithPagination;

    // public function render()
    // {
    //     $this->materiaPrima = MateriaPrimaModel::all();

    //     return view('livewire.admin.recetas', [
    //         'receta' => Receta::paginate(10),
    //     ]);
    // }
    public function render()
    {
        $this->filtrarMateriaPrima();
        // $this->materiaPrima = MateriaPrimaModel::all();
        $query = Receta::query();

        if ($this->busqueda) {
            $query->where('id', 'like', '%' . $this->busqueda . '%')
                ->orWhere('nombre', 'like', '%' . $this->busqueda . '%');
        }

        $receta = $query->paginate(10);

        return view('livewire.admin.recetas', [
            'receta' => $receta,
            'materiaPrima' => $this->materiaPrima,
        ]);
    }
    public function filtrarMateriaPrima()
    {
        $this->materiaPrima = MateriaPrimaModel::query();

        if ($this->searchMateriaPrima) {
            $this->materiaPrima->where('producto', 'like', '%' . $this->searchMateriaPrima . '%');
        }

        $this->materiaPrima = $this->materiaPrima->get();
    }
    public function mount()
    {
        $this->calcularCostoUnitario();
        $this->calcularPorcentaje();
    }
    public function crear()
    {
        // $this->
        // $this->limpiarCampos();
        $this->abrirModal();
    }
    public function abrirModal()
    {
        $this->modal = true;
    }
    public function cerrarModal()
    {
        $this->modal = false;
    }
    public function agregarMateriaPrima($id)
    {
        // Obtener información de la materia prima seleccionada desde tu modelo o fuente de datos
        $materiaSeleccionada = MateriaPrimaModel::find($id);

        // Agregar la materia prima seleccionada a la lista
        $this->materiaPrimaSeleccionada[] = [
            'id' => $materiaSeleccionada->id,
            'producto' => $materiaSeleccionada->producto,
            'peso' => $materiaSeleccionada->peso,
            'precio' => $materiaSeleccionada->precio,
            'stock' => $materiaSeleccionada->stock,
            // Agregar otros campos según tu estructura
        ];
        $this->searchMateriaPrima = '';

    }

    public function actualizarSubTotalCostoElaboracion()
    {
        $subTotal = collect($this->materiaPrimaSeleccionada)
            ->sum(function ($materia) {
                return $materia['costo_en_receta'] ?? 0;
            });

        $this->subTotalCostoElaboracion = $subTotal;
    }

    public function eliminarMateriaPrima($index)
    {
        if (isset($this->materiaPrimaSeleccionada[$index])) {
            unset($this->materiaPrimaSeleccionada[$index]);
            $this->actualizarSubTotalCostoElaboracion();
        }
    }
    public function calcularCostoEnReceta($index)
    {
        // Obtenemos los valores necesarios para el cálculo
        $cantidad = $this->materiaPrimaSeleccionada[$index]['cantidad'] ?? 0;
        $precio = $this->materiaPrimaSeleccionada[$index]['precio'] ?? 0;
        $peso = $this->materiaPrimaSeleccionada[$index]['peso'] ?? 0;
        $stock_rec = $this->materiaPrimaSeleccionada[$index]['stock'] ?? 0;

        // Realizamos el cálculo
        $costo_en_receta = $cantidad * $precio / $peso;
        //descontamos stock
        $nuevo_stock = $stock_rec - $cantidad;

        // Actualizamos el valor en el arreglo de materia prima seleccionada
        $this->materiaPrimaSeleccionada[$index]['costo_en_receta'] = $costo_en_receta;
        $this->materiaPrimaSeleccionada[$index]['stock'] = $nuevo_stock;
        $this->actualizarSubTotalCostoElaboracion();
    }
    public function sumarEmpleadoConSubTotal()
    {
        $this->costo_elaboracion = $this->empleado + $this->subTotalCostoElaboracion;
    }

    protected $listeners = [
        'empleadoUpdated',
        'subTotalCostoElaboracionUpdated',
        'costo_elaboracionUpdated',
    ];

    public function updatedEmpleado()
    {
        $this->sumarEmpleadoConSubTotal();
        $this->calcularCostoUnitario();
    }

    public function updatedSubTotalCostoElaboracion()
    {
        $this->sumarEmpleadoConSubTotal();
    }


    public function updatedPorciones()
    {
        $this->calcularCostoUnitario();
    }

    public function updatedCostoElaboracion()
    {
        $this->calcularCostoUnitario();
    }


    public function calcularCostoUnitario()
    {
        if ($this->porciones != 0) {
            $this->costo_unitario = $this->costo_elaboracion / $this->porciones;
        }
    }
    public function calcularPorcentaje()
    {

        $this->precio_unitario = $this->costo_unitario + (($this->costo_unitario * $this->porcentaje_ganancia) / 100);
    }
    public function calcularIva()
    {

        $this->precio_iva = $this->precio_unitario + $this->iva;
        $this->calcularGanancia();
    }
    public function calcularGanancia()
    {

        $this->ganancia = $this->precio_iva - $this->costo_unitario;
    }

    public function guardar()
    {
        // Empezar una transacción para asegurar que todas las operaciones se completen correctamente o se reviertan si hay un error
        DB::beginTransaction();

        try {
            $receta = Receta::find($this->id_receta);
            if (!$receta) {
                // Guardar en la tabla Receta
                $receta = Receta::Create([
                    'nombre' => $this->nombre,
                    'descripcion' => $this->descripcion,
                    'porciones' => $this->porciones,
                ]);
            } else {
                $receta->Update([
                    'nombre' => $this->nombre,
                    'descripcion' => $this->descripcion,
                    'porciones' => $this->porciones,
                ]);
            }
            $insumo = Insumo::updateOrCreate(
                ['id_receta' => $this->id_receta],
                [
                    'id_receta' => $receta->id,
                    // Otros campos según tu estructura de Insumos
                ]
            );

            // MateriaPrimaModel::updateOrCreate(
            //     ['id' => $this->id_materia],
            //     [
            //         'producto' => $this->producto,
            //         'precio' => $this->precio,
            //         'peso' => $this->peso,
            //         'stock' => $this->stock
            //     ]
            // );

            // Guardar en la tabla Calculos
            $calculo = Calculo::updateOrCreate(
                ['id_insumos' => $insumo->id],
                [
                    'empleado' => $this->empleado,
                    'costo_elaboracion' => $this->costo_elaboracion,
                    'costo_unitario' => $this->costo_unitario,
                    'porcentaje_ganancia' => $this->porcentaje_ganancia,
                    'precio' => $this->precio_unitario,
                    'iva' => $this->iva,
                    'precio_iva' => $this->precio_iva,
                    'ganancia' => $this->ganancia,
                ]
            );

            $datosPivot = [];
            foreach ($this->materiaPrimaSeleccionada as $materiaPrima) {
                $datosPivot[$materiaPrima['id']] = [
                    'cantidad' => $materiaPrima['cantidad'],
                    'costo_en_receta' => $materiaPrima['costo_en_receta'],
                    // Otros campos según tu estructura de la tabla pivot insumos_materia_prima
                ];
                // Obtén la materia prima actual para actualizar su stock
                $materiaPrimaModel = MateriaPrimaModel::find($materiaPrima['id']);
                if ($materiaPrimaModel) {
                    $materiaPrimaModel->stock = $materiaPrima['stock'];
                    $materiaPrimaModel->save();
                }
            }

            $insumo->materiasPrimas()->sync($datosPivot);

            $calculo->id_insumos = $insumo->id;
            $calculo->save();

            // Asignar el ID de Calculo e Insumo a la Receta
            $receta->id_calculos = $calculo->id;
            $receta->id_insumo = $insumo->id;
            $receta->save();

            // Confirmar la transacción
            DB::commit();

            // Limpiar los campos después de guardar
            $this->reset([
                'nombre', 'descripcion', 'porciones', 'costo_elaboracion', 'costo_unitario',
                'porcentaje_ganancia', 'precio', 'iva', 'precio_iva', 'ganancia',
                'materia_id', 'cantidad', 'unidad', 'costo_en_receta',
            ]);

            // Mensaje de éxito o redirección a otra página, etc.
        } catch (\Exception $e) {
            // En caso de error, revertir la transacción
            DB::rollback();
            // Manejar el error como desees (mensaje de error, registro en logs, etc.)
            // Registrar el error en el log
            Log::error('Ocurrió un error al guardar: ' . $e->getMessage());
            // Puedes registrar más detalles si lo deseas:
            Log::error($e);
        }

        $this->cerrarModal();
        // session()->flash(
        //     'message',
        //     $this->id_receta ? '¡Actualización exitosa!' : '¡Alta Exitosa!'
        // );
        // $this->limpiarCampos();
    }
    public function editar($id)
    {
        // Recuperar la receta con sus relaciones (calculos e insumos)
        $receta = Receta::with('calculo')->findOrFail($id);
        // dd($receta);


        $this->id_receta = $receta->id;
        // Ahora puedes acceder a los datos relacionados
        $this->nombre = $receta->nombre;
        $this->descripcion = $receta->descripcion;
        $this->porciones = $receta->porciones;
        // Acceder a los datos de Calculos
        $calculo = $receta->calculo;
        $this->costo_elaboracion = $calculo->costo_elaboracion;
        $this->empleado = $calculo->empleado;
        $this->costo_unitario = $calculo->costo_unitario;
        $this->porcentaje_ganancia = $calculo->porcentaje_ganancia;
        $this->precio = $calculo->precio;
        $this->iva = $calculo->iva;
        $this->precio_iva = $calculo->precio_iva;
        $this->ganancia = $calculo->ganancia;
        // Acceder a los datos de Insumos
        $insumos = Insumo::with('materiasPrimas')->findOrFail($receta->id_insumo);

        // dd($insumos);
        foreach ($insumos->materiasPrimas as $mat) {
            $this->materiaPrimaSeleccionada[] = [
                'id' => $mat->id,
                'producto' => $mat->producto,
                'peso' => $mat->peso,
                'precio' => $mat->precio, // O el campo que desees mostrar
                'stock' => $mat->stock,
                'cantidad' => $mat->pivot->cantidad,
                'costo_en_receta' => $mat->pivot->costo_en_receta,
                // Agregar otros campos según tu estructura
            ];
        }
        $this->abrirModal();
        $this->actualizarSubTotalCostoElaboracion();
    }
    public function borrar($id)
    {
        DB::beginTransaction();

        try {
            $receta = Receta::findOrFail($id);
            if ($receta) {
                $receta->delete();
            }
            // Confirmar la transacción
            DB::commit();

            // Mensaje de éxito o redirección a otra página, etc.
        } catch (\Exception $e) {
            // En caso de error, revertir la transacción
            DB::rollback();
            // Manejar el error como desees (mensaje de error, registro en logs, etc.)
            // Registro de error en el log
            Log::error('Ocurrió un error al eliminar la receta: ' . $e->getMessage());
            // Puedes registrar más detalles si lo deseas
            Log::error($e);
        }
    }
}
