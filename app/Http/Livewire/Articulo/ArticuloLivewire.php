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
    // --- NUEVAS VARIABLES PARA EDICIÓN DE ATRIBUTOS ---
    public $modo_edicion_atributo = false;
    public $atributo_id_editando = null;
    // --- NUEVO BLOQUE ---
    public $valores_seleccionados = []; // ['id_atributo' => [id_valor1, id_valor2, ...]]
    public $atributo_activo = null; // Atributo que se está desplegando



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
            $this->valores_seleccionados = [];
            $this->atributo_activo = null;
        } else {
            // Al activar variantes, no preseleccionar nada (siempre limpio)
            $this->atributos_seleccionados = [];
            $this->valores_seleccionados = [];
            $this->atributo_activo = null;
        }
    }

    public function updatedAtributosSeleccionados()
    {
        // Si el atributo que está abierto ya no está seleccionado, cerrarlo
        if ($this->atributo_activo && !in_array($this->atributo_activo, $this->atributos_seleccionados ?? [])) {
            $this->atributo_activo = null;
        }
        // No abrir nada automáticamente, solo cerrar si corresponde
    }


    public function generarVariantesSeleccionadas()
    {
        // Validaciones
        if (empty($this->atributos_seleccionados)) {
            session()->flash('error', 'Selecciona al menos un atributo.');
            return;
        }

        $tieneValores = false;
        foreach ($this->atributos_seleccionados as $idAtributo) {
            if (!empty($this->valores_seleccionados[$idAtributo] ?? [])) {
                $tieneValores = true;
                break;
            }
        }

        if (!$tieneValores) {
            session()->flash('error', 'Marca al menos un valor por atributo.');
            return;
        }

        // 🔄 PRESERVAR variantes existentes (no reemplazar)
        $variantesExistentes = $this->variantes_generadas;

        // Generar todas las combinaciones nuevas
        if (count($this->atributos_seleccionados) === 0) {
            $this->variantes_generadas = [];
            return;
        }

        $combinaciones = [[]];

        foreach ($this->atributos_seleccionados as $idAtributo) {
            $atributo = collect($this->atributos_disponibles)->firstWhere('id_atributo', $idAtributo);
            if (!$atributo) continue;

            $valoresIdsSeleccionados = $this->valores_seleccionados[$idAtributo] ?? [];
            if (empty($valoresIdsSeleccionados)) continue;

            $valores = $atributo->valores->whereIn('id_valor', $valoresIdsSeleccionados);

            $nuevasCombinaciones = [];
            foreach ($combinaciones as $combinacion) {
                foreach ($valores as $valor) {
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

        // Crear hash único para cada combinación
        // Empezar con todas las existentes y solo agregar las nuevas distintas
        $nuevasVariantes = $variantesExistentes;
        $hashesExistentes = collect($variantesExistentes)->pluck('hash')->filter()->values()->all();
        foreach ($combinaciones as $combinacion) {
            $descripcion = collect($combinacion)->map(function ($item) {
                return $item['nombre_atributo'] . ': ' . $item['valor'];
            })->join(', ');

            $hash = md5(collect($combinacion)->pluck('id_valor')->sort()->join('-'));

            // Si ya existe por hash, saltar; sino agregar como nueva
            if (in_array($hash, $hashesExistentes, true)) {
                continue;
            } else {
                // Nueva variante
                $nuevasVariantes[] = [
                    'hash' => $hash,
                    'combinacion' => $combinacion,
                    'descripcion' => $descripcion,
                    'precio' => $this->precio_unitario ?? '',
                    'stock' => '',
                    'sku_custom' => '',
                    'activa' => true
                ];
                $hashesExistentes[] = $hash; // mantener cache local
            }
        }

        $this->variantes_generadas = $nuevasVariantes;

        // Limpiar selección
        $this->valores_seleccionados = [];
        $this->atributo_activo = null;

        session()->flash('message', 'Variantes generadas correctamente');
    }

    private function guardarVariantes($articulo)
    {
        if ($this->articulo_id) {
            ArticuloVariante::where('idarticulo', $articulo->idarticulo)->delete();
        }

        $primerVariante = true;
        // Normalizar variantes para que siempre tengan 'combinacion'
        foreach ($this->variantes_generadas as &$variante) {
            if (!isset($variante['combinacion']) && isset($variante['atributos'])) {
                $variante['combinacion'] = $variante['atributos'];
            }
        }
        unset($variante); // buena práctica con referencias


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

            // Validar que los id_valor existan y pertenezcan al local
            $valoresIds = collect($variante['combinacion'])
                ->pluck('id_valor')
                ->filter()
                ->unique()
                ->values()
                ->all();

            if (!empty($valoresIds)) {
                $validos = AtributoValor::whereIn('id_valor', $valoresIds)->pluck('id_valor')->all();
                if (!empty($validos)) {
                    $nuevaVariante->atributoValores()->attach($validos);
                }
            }

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


    // public function cerrarModalAtributos()
    // {
    //     $this->mostrar_modal_atributos = false;
    //     $this->resetCamposAtributo();
    // }

    private function resetCamposAtributo()
    {
        $this->atributo_nombre = '';
        $this->atributo_tipo = 'select';
        $this->atributo_obligatorio = false;
        $this->atributo_valores = [];
        $this->nuevo_valor = '';
        $this->editando_atributo = null;
    }

    public function toggleValores($atributo_id)
    {
        if (!isset($this->valores_seleccionados[$atributo_id])) {
            $this->valores_seleccionados[$atributo_id] = [];
        }

        $this->atributo_activo = $this->atributo_activo === $atributo_id ? null : $atributo_id;
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
    public function editarAtributo($id)
    {
        $atributo = Atributo::with('valores')->findOrFail($id);

        $this->atributo_id_editando = $atributo->id_atributo;
        $this->atributo_nombre = $atributo->nombre;
        $this->atributo_tipo = $atributo->tipo;
        $this->atributo_obligatorio = $atributo->obligatorio;
        $this->atributo_valores = $atributo->valores->map(function ($valor) {
            return [
                'valor' => $valor->valor,
                'color_hex' => $valor->color_hex,
                'id_valor' => $valor->id_valor,
            ];
        })->toArray();

        $this->modo_edicion_atributo = true;
        $this->mostrar_modal_atributos = true;
    }

    public function guardarCambiosAtributo()
    {
        $atributo = Atributo::findOrFail($this->atributo_id_editando);
        $atributo->update([
            'nombre' => $this->atributo_nombre,
            'tipo' => $this->atributo_tipo,
            'obligatorio' => $this->atributo_obligatorio,
        ]);

        // Eliminar valores viejos y volver a crear
        AtributoValor::where('id_atributo', $atributo->id_atributo)->delete();

        foreach ($this->atributo_valores as $valor) {
            AtributoValor::create([
                'id_atributo' => $atributo->id_atributo,
                'valor' => $valor['valor'],
                'color_hex' => $valor['color_hex'] ?? null,
            ]);
        }

        $this->cerrarModalAtributos();
        $this->atributos_disponibles = $this->getAtributos(); // 🔁 refresca la lista
        $this->dispatchBrowserEvent('notificacion', ['message' => 'Atributo actualizado correctamente']);
    }

    public function cerrarModalAtributos()
    {
        $this->mostrar_modal_atributos = false;
        $this->modo_edicion_atributo = false;
        $this->atributo_id_editando = null;
        $this->atributo_nombre = '';
        $this->atributo_tipo = 'select';
        $this->atributo_obligatorio = false;
        $this->atributo_valores = [];
        $this->nuevo_valor = '';
    }
    public function agregarVarianteManual()
    {
        // Si no se eligió nada, no hace nada
        if (empty($this->valores_seleccionados)) {
            session()->flash('error', 'Seleccioná al menos un valor para crear la variante.');
            return;
        }

        $descripcion = [];
        $atributos_variante = [];

        // Recorremos los atributos que tengan valores seleccionados
        foreach ($this->valores_seleccionados as $idAtributo => $valoresIds) {
            if (empty($valoresIds)) continue;

            // Buscamos el atributo desde los disponibles
            $atributo = collect($this->atributos_disponibles)
                ->firstWhere('id_atributo', $idAtributo);
            if (!$atributo) continue;

            // Por simplicidad, tomamos el primer valor marcado (podés ajustar para permitir varios)
            $valor = $atributo->valores
                ->firstWhere('id_valor', $valoresIds[0]);

            if ($valor) {
                $descripcion[] = "{$atributo->nombre}: {$valor->valor}";
                $atributos_variante[] = [
                    'id_atributo' => $atributo->id_atributo,
                    'id_valor' => $valor->id_valor,
                    'nombre_atributo' => $atributo->nombre,
                    'valor' => $valor->valor,
                ];
            }
        }

        if (empty($atributos_variante)) {
            session()->flash('error', 'No se pudo crear la variante, faltan valores válidos.');
            return;
        }

        // Generamos hash único para deduplicar
        $hash = md5(collect($atributos_variante)->pluck('id_valor')->sort()->join('-'));

        // Evitar duplicados si ya existe
        $yaExiste = collect($this->variantes_generadas)->contains(function ($v) use ($hash) {
            return ($v['hash'] ?? null) === $hash;
        });

        if ($yaExiste) {
            session()->flash('error', 'Esa combinación ya existe.');
        } else {
            // Agregar usando formato unificado compatible con guardado
            $this->variantes_generadas[] = [
                'hash' => $hash,
                'combinacion' => $atributos_variante,
                'descripcion' => implode(', ', $descripcion),
                'precio' => '',
                'stock' => '',
                'sku_custom' => '',
                'activa' => true,
            ];
            session()->flash('message', 'Variante agregada correctamente');
        }

        // Limpiamos selección
        $this->valores_seleccionados = [];
        $this->atributo_activo = null;
    }

    public function eliminarVariante($hash)
    {
        if (empty($hash)) {
            return;
        }
        $this->variantes_generadas = collect($this->variantes_generadas)
            ->reject(function ($v) use ($hash) {
                return ($v['hash'] ?? null) === $hash;
            })
            ->values()
            ->all();
        session()->flash('message', 'Variante eliminada');
    }

    public function eliminarAtributoDef($idAtributo)
    {
        try {
            if (!$idAtributo) return;

            // Validar que no esté siendo usado por variantes actuales en edición/creación
            $estaEnUso = collect($this->variantes_generadas)->contains(function ($var) use ($idAtributo) {
                $comb = $var['combinacion'] ?? ($var['atributos'] ?? []);
                return collect($comb)->pluck('id_atributo')->contains($idAtributo);
            });

            if ($estaEnUso) {
                session()->flash('error', 'No se puede eliminar: el atributo está en uso por alguna variante.');
                return;
            }

            // Desactivar en BD (soft delete lógica por estado)
            $atributo = Atributo::findOrFail($idAtributo);
            $atributo->update(['estado' => 'inactivo']);

            // Sacarlo de seleccionados y refrescar catálogo de atributos
            $this->atributos_seleccionados = array_values(array_filter($this->atributos_seleccionados, function ($id) use ($idAtributo) {
                return (int) $id !== (int) $idAtributo;
            }));
            unset($this->valores_seleccionados[$idAtributo]);
            if ($this->atributo_activo === $idAtributo) {
                $this->atributo_activo = null;
            }

            $this->atributos_disponibles = $this->getAtributos();
            session()->flash('message', 'Atributo eliminado correctamente');
        } catch (\Exception $e) {
            Log::error('Error al eliminar atributo: ' . $e->getMessage());
            session()->flash('error', 'No se pudo eliminar el atributo');
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

        // 🔧 Compatibilidad: puede venir como 'atributos' o como 'combinacion'
        $valores = collect($variante['atributos'] ?? $variante['combinacion'] ?? [])
            ->pluck('valor')
            ->toArray();

        return ArticuloVariante::generarSku($articuloTemp, $valores);
    }
}
