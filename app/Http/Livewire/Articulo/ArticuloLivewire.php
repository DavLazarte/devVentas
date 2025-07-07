<?php

namespace App\Http\Livewire\Articulo;

use Livewire\Component;
use App\Models\Articulo;
use App\Models\Categoria;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;


use Livewire\WithFileUploads;

class ArticuloLivewire extends Component
{

    use WithFileUploads;

    public $articulos, $articulo_id, $imagen, $imagen_actual, $nombre, $descripcion, $stock, $codigo, $precio_unitario, $busqueda, $categorias, $categoria_id, $recetas, $receta, $recetaSeleccionada;
    public $isOpen = 0;
    public $modoEdit = 0;
    public $loading = false;
    public $estado = 'inactivo';
    public $mostrar_feed = 0;
    public $destacado = 0;

    // En tus componentes Livewire (por ejemplo, CategoriaLivewire)
    public $layout = 'sistema';

    protected $listeners = [
        'editarArticulo' => 'editar',
        'openModal' => 'openModal'
    ];

    public function mount()
    {
        $this->categorias = $this->getCategorias();
    }

    public function getCategorias()
    {
        $idLocal = Auth::user()->local->id;
        return Categoria::where('id_local', $idLocal)->pluck('nombre', 'id_categoria')->toArray();
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
        $this->mostrar_feed = 0;
        $this->destacado = 0;
        $this->articulo_id = '';
        $this->stock = '';
        $this->precio_unitario = '';
        $this->codigo = '';
        $this->imagen = '';
    }
    public function guardar()
    {
        $this->loading = true; // Bloquea el botón y muestra el loader
        // Simulación de un proceso (quita esto en producción)
        // sleep(2);

        // Forzar a enteros antes de validar y guardar
        $this->mostrar_feed = (int) $this->mostrar_feed;
        $this->destacado = (int) $this->destacado;

        $reglas = [
            'nombre' => 'required',
            'descripcion' => 'required',
            'estado' => 'required|in:activo,inactivo',
            'destacado' => 'boolean',
            'mostrar_feed' => 'boolean',
        ];

        if ($this->imagen) {
            $reglas['imagen'] = 'image|max:2048';
        }




        try {
            $this->validate($reglas);

            $idLocal = auth()->user()->local->id;
            $nombreArchivo = null;

            if ($this->imagen) {
                $nombreLimpio = str_replace(' ', '_', strtolower($this->nombre));
                // $extension = $this->imagen->getClientOriginalExtension();

                // Si el artículo ya existe, usamos su ID, si no, generamos un nombre temporal.
                $idArticulo = $this->articulo_id ?? uniqid();
                $nombreArchivo = "{$nombreLimpio}_{$idArticulo}.webp";

                // $rutaCarpeta = "locales/{$idLocal}/articulos"; produ
                $rutaCarpeta = "public/locales/{$idLocal}/articulos";

                if (!Storage::exists($rutaCarpeta)) {
                    Storage::makeDirectory($rutaCarpeta);
                }

                // Procesamiento de imagen con Intervention
                $img = Image::make($this->imagen->getRealPath())
                    ->resize(800, null, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize(); // no agrandar si es más chica
                    })
                    ->encode('webp', 80); // calidad entre 0 y 100

                Storage::put("{$rutaCarpeta}/{$nombreArchivo}", $img);

                // $this->imagen->storeAs($rutaCarpeta, $nombreArchivo);
            }

            $articulo = Articulo::updateOrCreate(['idarticulo' => $this->articulo_id], [
                'idcategoria' => $this->categoria_id,
                'nombre' => $this->nombre,
                'descripcion' => $this->descripcion,
                'codigo' => $this->codigo,
                'precio_unitario' => $this->precio_unitario,
                'stock' => $this->stock,
                'estado' => $this->estado,
                'mostrar_feed' => $this->mostrar_feed,
                'destacado' => $this->destacado,
                'imagen' => $nombreArchivo ? "locales/{$idLocal}/articulos/{$nombreArchivo}" : ($this->imagen_actual ?? null),
                'id_local' => $idLocal
            ]);

            session()->flash(
                'message',
                $this->articulo_id ? 'Artículo actualizado exitosamente.' : 'Artículo creado exitosamente.'
            );

            $this->emit('refreshDatatableArticulos');
            $this->closeModal();
            $this->resetInputFields();
        } catch (\Exception $e) {
            Log::error("Error al guardar el artículo: " . $e->getMessage());
            session()->flash('error', 'Ocurrió un error al guardar el artículo.' . $e->getMessage());
        } finally {
            $this->loading = false; // Reactiva el botón
        }
    }


    public function editar($id)
    {
        $this->modoEdit = true;
        $articulo = Articulo::findOrFail($id);
        $this->articulo_id = $id;
        // $this->categorias = $articulo->categoria->nombre;
        $this->categoria_id = $articulo->idcategoria;
        $this->nombre = $articulo->nombre;
        $this->descripcion = $articulo->descripcion;
        $this->codigo = $articulo->codigo;
        $this->precio_unitario = floatval($articulo->precio_unitario);
        $this->stock = $articulo->stock;
        $this->estado = $articulo->estado;
        $this->mostrar_feed = $articulo->mostrar_feed;
        $this->destacado = $articulo->destacado;

        // Cargar la imagen actual
        $this->imagen_actual = $articulo->imagen;

        $this->openModal();
    }

    public function borrar($id)
    {
        Articulo::find($id)->delete();
        session()->flash('message', 'Articulo eliminado exitosamente.');
        $this->emit('refreshDatatableArticulos');
    }
}
