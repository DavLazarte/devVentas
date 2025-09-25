<?php

namespace App\Http\Livewire\Articulo;

use Livewire\Component;
use App\Models\Articulo;
use App\Models\ArticuloVariante;
use App\Models\Atributo;
use App\Models\AtributoValor;
use App\Models\Categoria;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Livewire\WithFileUploads;

class ArticuloLivewire extends Component
{
    use WithFileUploads;

    // Propiedades originales
    public $articulos, $articulo_id, $imagen, $imagen_actual, $nombre, $descripcion, $stock, $codigo, $precio_unitario, $busqueda, $categorias, $categoria_id, $recetas, $receta, $recetaSeleccionada;
    public $isOpen = 0;
    public $modoEdit = 0;
    public $loading = false;
    public $estado = 'activo'; // Cambiado a activo por defecto
    public $mostrar_feed = false;
    public $destacado = false;

    // Nuevas propiedades para variantes
    public $tiene_variantes = false;
    public $atributos_disponibles = [];
    public $atributos_seleccionados = [];
    public $variantes_generadas = [];
    public $mostrar_seccion_variantes = false;
    // Propiedades para gestión de atributos
    public $mostrar_modal_atributos = false;
    public $atributo_nombre = '';
    public $atributo_tipo = 'select';
    public $atributo_obligatorio = false;
    public $atributo_valores = [];
    public $nuevo_valor = '';
    public $editando_atributo = null;

    public $layout = 'sistema';

    protected $listeners = [
        'editarArticulo' => 'editar',
        'openModal' => 'openModal'
    ];

    public function mount()
    {
        $this->categorias = $this->getCategorias();
        $this->atributos_disponibles = $this->getAtributos();
    }

    public function getCategorias()
    {
        $idLocal = Auth::user()->local->id;
        return Categoria::where('id_local', $idLocal)->pluck('nombre', 'id_categoria')->toArray();
    }

    public function getAtributos()
    {
        $idLocal = Auth::user()->local->id;
        return Atributo::where('id_local', $idLocal)
            ->where('estado', 'activo')
            ->with('valores')
            ->orderBy('orden')
            ->get();
    }

    public function render()
    {
        return view('livewire.articulo.articulo-livewire');
    }

    public function crear()
    {
        $this->resetInputFields();
        $this->openModal();
    }

    public function openModal()
    {
        $this->isOpen = true;
    }

    public function closeModal()
    {
        $this->isOpen = false;
        $this->modoEdit = false;
    }

    private function resetInputFields()
    {
        $this->nombre = '';
        $this->descripcion = '';
        $this->estado = 'activo';
        $this->mostrar_feed = false;
        $this->destacado = false;
        $this->articulo_id = '';
        $this->stock = '';
        $this->precio_unitario = '';
        $this->codigo = '';
        $this->imagen = '';
        $this->imagen_actual = null;

        // Reset variantes
        $this->tiene_variantes = false;
        $this->atributos_seleccionados = [];
        $this->variantes_generadas = [];
        $this->mostrar_seccion_variantes = false;
    }

    // NUEVA: Método para manejar cambios en tiene_variantes
    public function updatedTieneVariantes($value)
    {
        $this->tiene_variantes = (bool) $value;
        $this->mostrar_seccion_variantes = $this->tiene_variantes;

        if (!$this->tiene_variantes) {
            $this->atributos_seleccionados = [];
            $this->variantes_generadas = [];
        }
    }

    public function updatedAtributosSeleccionados()
    {
        // Si estamos editando y ya hay variantes cargadas, preguntar antes de regenerar
        if ($this->modoEdit && !empty($this->variantes_generadas)) {
            // NO regenerar automáticamente, mostrar advertencia
            return;
        }

        if ($this->tiene_variantes && !empty($this->atributos_seleccionados)) {
            $this->generarVariantes();
        } else {
            $this->variantes_generadas = [];
        }
    }

    // NUEVO MÉTODO para regenerar manualmente
    public function regenerarVariantes()
    {
        if ($this->tiene_variantes && !empty($this->atributos_seleccionados)) {
            $this->generarVariantes();
        }
    }

    private function generarVariantes()
    {
        if (count($this->atributos_seleccionados) === 0) {
            $this->variantes_generadas = [];
            return;
        }

        // Generar todas las combinaciones posibles
        $combinaciones = [[]];

        foreach ($this->atributos_seleccionados as $idAtributo) {
            $atributo = collect($this->atributos_disponibles)->firstWhere('id_atributo', $idAtributo);
            if (!$atributo) continue;

            $nuevasCombinaciones = [];
            foreach ($combinaciones as $combinacion) {
                foreach ($atributo->valores as $valor) {
                    $nuevaCombinacion = $combinacion;
                    $nuevaCombinacion[] = [
                        'id_atributo' => $idAtributo,
                        'id_valor' => $valor->id_valor,
                        'nombre_atributo' => $atributo->nombre,
                        'valor' => $valor->valor
                    ];
                    $nuevasCombinaciones[] = $nuevaCombinacion;
                }
            }
            $combinaciones = $nuevasCombinaciones;
        }

        // Crear hash único para cada combinación para identificarlas
        $nuevasVariantes = [];
        foreach ($combinaciones as $combinacion) {
            $descripcion = collect($combinacion)->map(function ($item) {
                return $item['nombre_atributo'] . ': ' . $item['valor'];
            })->join(', ');

            // Crear hash único basado en los valores de atributos
            $hash = md5(collect($combinacion)->pluck('id_valor')->sort()->join('-'));

            // Buscar si ya existe esta combinación
            $varianteExistente = collect($this->variantes_generadas)->firstWhere('hash', $hash);

            if ($varianteExistente) {
                // Ya existe, mantener precio y stock actual
                $nuevasVariantes[] = $varianteExistente;
            } else {
                // Nueva variante, usar valores por defecto
                $nuevasVariantes[] = [
                    'hash' => $hash,
                    'combinacion' => $combinacion,
                    'descripcion' => $descripcion,
                    'precio' => $this->precio_unitario ?? 25.00,
                    'stock' => 10,
                    'sku_custom' => '', // SKU personalizado vacío
                    'activa' => true
                ];
            }
        }

        $this->variantes_generadas = $nuevasVariantes;
    }

    private function guardarVariantes($articulo)
    {
        if ($this->articulo_id) {
            ArticuloVariante::where('idarticulo', $articulo->idarticulo)->delete();
        }

        $primerVariante = true;

        foreach ($this->variantes_generadas as $variante) {
            if (!$variante['activa']) continue;

            // Usar SKU personalizado si existe, sino generar automático
            $sku = !empty($variante['sku_custom'])
                ? $variante['sku_custom']
                : ArticuloVariante::generarSku($articulo, collect($variante['combinacion'])->pluck('valor')->toArray());

            $nuevaVariante = ArticuloVariante::create([
                'idarticulo' => $articulo->idarticulo,
                'sku' => $sku,
                'precio_unitario' => $variante['precio'],
                'stock' => $variante['stock'],
                'descripcion_variante' => $variante['descripcion'],
                'estado' => 'activo',
                'es_variante_principal' => $primerVariante
            ]);

            $valoresIds = collect($variante['combinacion'])->pluck('id_valor')->toArray();
            $nuevaVariante->atributoValores()->attach($valoresIds);

            $primerVariante = false;
        }
    }

    public function guardar()
    {
        $this->loading = true;

        // Normalizar valores booleanos
        $this->mostrar_feed = (bool) $this->mostrar_feed;
        $this->destacado = (bool) $this->destacado;
        $this->tiene_variantes = (bool) $this->tiene_variantes;

        // Reglas de validación
        $reglas = [
            'nombre' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'estado' => 'required|in:activo,inactivo',
            'destacado' => 'boolean',
            'mostrar_feed' => 'boolean',
            'tiene_variantes' => 'boolean',
            'categoria_id' => 'required|exists:categorias,id_categoria'
        ];

        // Validaciones condicionales
        if (!$this->tiene_variantes) {
            $reglas['precio_unitario'] = 'required|numeric|min:0';
            $reglas['stock'] = 'required|integer|min:0';
        } else {
            // Validar que tenga al menos una variante activa
            $variantesActivas = collect($this->variantes_generadas)->where('activa', true);
            if ($variantesActivas->isEmpty()) {
                session()->flash('error', 'Debe tener al menos una variante activa.');
                $this->loading = false;
                return;
            }
        }

        try {
            $this->validate($reglas);

            $idLocal = auth()->user()->local->id;
            $nombreArchivo = null;

            // Procesar imagen si existe
            if ($this->imagen) {
                $nombreLimpio = str_replace(' ', '_', strtolower($this->nombre));
                $idArticulo = $this->articulo_id ?? uniqid();
                $nombreArchivo = "{$nombreLimpio}_{$idArticulo}.webp";
                $rutaCarpeta = "locales/{$idLocal}/articulos";

                if (!Storage::exists($rutaCarpeta)) {
                    Storage::makeDirectory($rutaCarpeta);
                }

                $img = Image::make($this->imagen->getRealPath())
                    ->resize(800, null, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    })
                    ->encode('webp', 80);

                Storage::put("{$rutaCarpeta}/{$nombreArchivo}", $img);
            }

            // Crear o actualizar artículo
            $articulo = Articulo::updateOrCreate(['idarticulo' => $this->articulo_id], [
                'idcategoria' => $this->categoria_id,
                'nombre' => $this->nombre,
                'descripcion' => $this->descripcion,
                'codigo' => $this->codigo,
                'precio_unitario' => $this->tiene_variantes ? 0 : $this->precio_unitario,
                'stock' => $this->tiene_variantes ? 0 : $this->stock,
                'estado' => $this->estado,
                'mostrar_feed' => $this->mostrar_feed,
                'destacado' => $this->destacado,
                'tiene_variantes' => $this->tiene_variantes,
                'imagen' => $nombreArchivo ? "locales/{$idLocal}/articulos/{$nombreArchivo}" : ($this->imagen_actual ?? null),
                'id_local' => $idLocal
            ]);

            // Guardar variantes si existen
            if ($this->tiene_variantes && !empty($this->variantes_generadas)) {
                $this->guardarVariantes($articulo);
            }

            session()->flash(
                'message',
                $this->articulo_id ? 'Artículo actualizado exitosamente.' : 'Artículo creado exitosamente.'
            );

            $this->emit('refreshDatatableArticulos');
            $this->closeModal();
            $this->resetInputFields();
        } catch (\Illuminate\Validation\ValidationException $e) {
            session()->flash('error', 'Error de validación: ' . collect($e->errors())->flatten()->first());
        } catch (\Exception $e) {
            Log::error("Error al guardar el artículo: " . $e->getMessage());
            session()->flash('error', 'Ocurrió un error al guardar el artículo: ' . $e->getMessage());
        } finally {
            $this->loading = false;
        }
    }

    public function editar($id)
    {
        $this->modoEdit = true;
        $articulo = Articulo::with('variantes.atributoValores.atributo')->findOrFail($id);

        $this->articulo_id = $id;
        $this->categoria_id = $articulo->idcategoria;
        $this->nombre = $articulo->nombre;
        $this->descripcion = $articulo->descripcion;
        $this->codigo = $articulo->codigo;
        $this->precio_unitario = floatval($articulo->precio_unitario);
        $this->stock = $articulo->stock;
        $this->estado = $articulo->estado;
        $this->mostrar_feed = $articulo->mostrar_feed;
        $this->destacado = $articulo->destacado;
        $this->imagen_actual = $articulo->imagen;

        // Cargar datos de variantes si existen
        $this->tiene_variantes = $articulo->tiene_variantes;

        if ($this->tiene_variantes && $articulo->variantes->count() > 0) {
            $this->mostrar_seccion_variantes = true;
            $this->cargarVariantesExistentes($articulo);
        }

        $this->openModal();
    }

    private function cargarVariantesExistentes($articulo)
    {
        $atributosUtilizados = [];
        $this->variantes_generadas = [];
    
        foreach ($articulo->variantes as $variante) {
            $combinacion = [];
            foreach ($variante->atributoValores as $atributoValor) {
                $atributosUtilizados[] = $atributoValor->atributo->id_atributo;
                $combinacion[] = [
                    'id_atributo' => $atributoValor->atributo->id_atributo,
                    'id_valor' => $atributoValor->id_valor,
                    'nombre_atributo' => $atributoValor->atributo->nombre,
                    'valor' => $atributoValor->valor
                ];
            }
            
            $hash = md5(collect($combinacion)->pluck('id_valor')->sort()->join('-'));
    
            $this->variantes_generadas[] = [
                'hash' => $hash,
                'combinacion' => $combinacion,
                'descripcion' => $variante->descripcion_variante,
                'precio' => $variante->precio_unitario,
                'stock' => $variante->stock,
                'sku_custom' => $variante->sku, // Cargar SKU actual como personalizado
                'activa' => $variante->estado === 'activo'
            ];
        }
    
        $this->atributos_seleccionados = array_unique($atributosUtilizados);
    }
    public function abrirModalAtributos()
    {
        $this->mostrar_modal_atributos = true;
        $this->resetCamposAtributo();
    }

    public function cerrarModalAtributos()
    {
        $this->mostrar_modal_atributos = false;
        $this->resetCamposAtributo();
    }

    private function resetCamposAtributo()
    {
        $this->atributo_nombre = '';
        $this->atributo_tipo = 'select';
        $this->atributo_obligatorio = false;
        $this->atributo_valores = [];
        $this->nuevo_valor = '';
        $this->editando_atributo = null;
    }

    public function agregarValor()
    {
        if (!empty(trim($this->nuevo_valor))) {
            $this->atributo_valores[] = [
                'valor' => trim($this->nuevo_valor),
                'color_hex' => $this->atributo_tipo === 'color' ? '#000000' : null
            ];
            $this->nuevo_valor = '';
        }
    }

    public function eliminarValor($index)
    {
        unset($this->atributo_valores[$index]);
        $this->atributo_valores = array_values($this->atributo_valores);
    }

    public function guardarAtributo()
    {
        $this->validate([
            'atributo_nombre' => 'required|string|max:100',
            'atributo_tipo' => 'required|in:color,talla,texto,numero,select',
            'atributo_valores' => 'required|array|min:1'
        ]);

        try {
            $idLocal = auth()->user()->local->id;

            $atributo = Atributo::create([
                'nombre' => $this->atributo_nombre,
                'tipo' => $this->atributo_tipo,
                'obligatorio' => $this->atributo_obligatorio,
                'id_local' => $idLocal,
                'estado' => 'activo',
                'orden' => Atributo::where('id_local', $idLocal)->count() + 1
            ]);

            foreach ($this->atributo_valores as $index => $valor) {
                AtributoValor::create([
                    'id_atributo' => $atributo->id_atributo,
                    'valor' => $valor['valor'],
                    'color_hex' => $valor['color_hex'],
                    'orden' => $index + 1,
                    'estado' => 'activo'
                ]);
            }

            // Recargar atributos
            $this->atributos_disponibles = $this->getAtributos();

            session()->flash('message', 'Atributo creado exitosamente.');
            $this->cerrarModalAtributos();
        } catch (\Exception $e) {
            session()->flash('error', 'Error al crear el atributo: ' . $e->getMessage());
        }
    }

    public function borrar($id)
    {
        try {
            $articulo = Articulo::findOrFail($id);

            // Si tiene variantes, eliminarlas primero
            if ($articulo->tiene_variantes) {
                ArticuloVariante::where('idarticulo', $id)->delete();
            }

            $articulo->delete();

            session()->flash('message', 'Artículo eliminado exitosamente.');
            $this->emit('refreshDatatableArticulos');
        } catch (\Exception $e) {
            Log::error("Error al eliminar artículo: " . $e->getMessage());
            session()->flash('error', 'Error al eliminar el artículo.');
        }
    }
    public function previsualizarSku($variante)
    {
        if (empty($this->codigo)) {
            return 'Ingresa código primero';
        }

        $articuloTemp = (object) [
            'codigo' => $this->codigo,
            'idarticulo' => $this->articulo_id ?? 999
        ];

        $valores = collect($variante['combinacion'])->pluck('valor')->toArray();
        return ArticuloVariante::generarSku($articuloTemp, $valores);
    }
}
