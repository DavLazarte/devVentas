<?php

namespace App\Http\Livewire\Articulo;

use Livewire\Component;
use App\Models\Articulo;
use App\Models\Categoria;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

use Livewire\WithFileUploads;

class ArticuloLivewire extends Component
{

    use WithFileUploads;

    public $articulos, $articulo_id, $imagen, $imagen_actual, $nombre, $descripcion, $estado, $stock, $codigo, $precio_unitario, $busqueda, $categorias, $categoria_id, $recetas, $receta, $recetaSeleccionada;
    public $isOpen = 0;
    public $modoEdit = 0;
    public $loading = false;

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
        $this->estado = '';
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

        try {
            $this->validate([
                'nombre' => 'required',
                'descripcion' => 'required',
                'codigo' => 'required',
                'estado' => 'required|in:activo,inactivo',
                'imagen' => 'nullable|image|max:2048',
            ]);

            $idLocal = auth()->user()->local->id;
            $nombreArchivo = null;

            if ($this->imagen) {
                $nombreLimpio = str_replace(' ', '_', strtolower($this->nombre));
                $extension = $this->imagen->getClientOriginalExtension();

                // Si el artículo ya existe, usamos su ID, si no, generamos un nombre temporal.
                $idArticulo = $this->articulo_id ?? uniqid();
                $nombreArchivo = "{$nombreLimpio}_{$idArticulo}.{$extension}";

                $rutaCarpeta = "locales/{$idLocal}/articulos";
                // $rutaCarpeta = "public/locales/{$idLocal}/articulos";

                if (!Storage::exists($rutaCarpeta)) {
                    Storage::makeDirectory($rutaCarpeta);
                }

                $this->imagen->storeAs($rutaCarpeta, $nombreArchivo);
            }

            $articulo = Articulo::updateOrCreate(['idarticulo' => $this->articulo_id], [
                'idcategoria' => $this->categoria_id,
                'nombre' => $this->nombre,
                'descripcion' => $this->descripcion,
                'codigo' => $this->codigo,
                'precio_unitario' => $this->precio_unitario,
                'stock' => $this->stock,
                'estado' => $this->estado,
                'imagen' => $nombreArchivo ? "locales/{$idLocal}/articulos/{$nombreArchivo}" : null,
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
