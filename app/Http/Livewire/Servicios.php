<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Servicio;
use App\Models\Categoria;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

use Livewire\WithFileUploads;

class Servicios extends Component
{

    use WithFileUploads;

    public $servicios, $servicio_id, $mostrar_feed, $destacado, $imagen, $imagen_actual, $nombre, $descripcion, $estado, $precio, $duracion, $busqueda, $categorias, $categoria_id;
    public $isOpen = 0;
    public $modoEdit = 0;
    public $loading = false;

    // En tus componentes Livewire (por ejemplo, CategoriaLivewire)
    public $layout = 'sistema';

    protected $listeners = [
        'editarServicio' => 'editar',
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
        $this->mostrar_feed = '';
        $this->destacado = '';
        $this->servicio_id = '';
        $this->precio = '';
        $this->duracion = '';
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
                'estado' => 'boolean',
                'destacado' => 'boolean',
                'mostrar_feed' => 'boolean',
                'imagen' => 'nullable|image|max:2048',
            ]);


            $idLocal = auth()->user()->local->id;
            $nombreArchivo = null;

            if ($this->imagen) {
                $nombreLimpio = str_replace(' ', '_', strtolower($this->nombre));
                $extension = $this->imagen->getClientOriginalExtension();

                // Si el servicio ya existe, usamos su ID, si no, generamos un nombre temporal.
                $idServicio = $this->servicio_id ?? uniqid();
                $nombreArchivo = "{$nombreLimpio}_{$idServicio}.{$extension}";

                 $rutaCarpeta = "locales/{$idLocal}/servicios";
                // $rutaCarpeta = "public/locales/{$idLocal}/servicios"; descomentar para  local

                if (!Storage::exists($rutaCarpeta)) {
                    Storage::makeDirectory($rutaCarpeta);
                }

                $this->imagen->storeAs($rutaCarpeta, $nombreArchivo);
            }

            $servicio = Servicio::updateOrCreate(['idservicio' => $this->servicio_id], [
                'idcategoria' => $this->categoria_id,
                'nombre' => $this->nombre,
                'descripcion' => $this->descripcion,
                'precio' => $this->precio,
                'duracion' => $this->duracion,
                'estado' => $this->estado,
                'mostrar_feed' => $this->mostrar_feed,
                'destacado' => $this->destacado,
                'imagen' => $nombreArchivo ? "locales/{$idLocal}/servicios/{$nombreArchivo}" : null,
                'id_local' => $idLocal
            ]);

            session()->flash(
                'message',
                $this->servicio_id ? 'Servicio actualizado exitosamente.' : 'Servicio creado exitosamente.'
            );

            $this->emit('refreshDatatableServicios');
            $this->closeModal();
            $this->resetInputFields();
        } catch (\Exception $e) {
            Log::error("Error al guardar el servicio: " . $e->getMessage());
            session()->flash('error', 'Ocurrió un error al guardar el servicio.' . $e->getMessage());
        } finally {
            $this->loading = false; // Reactiva el botón
        }
    }


    public function editar($id)
    {
        $this->modoEdit = true;
        $servicio = Servicio::findOrFail($id);
        $this->servicio_id = $id;
        // $this->categorias =$servicio->categoria->nombre;
        $this->categoria_id = $servicio->idcategoria;
        $this->nombre = $servicio->nombre;
        $this->descripcion = $servicio->descripcion;
        $this->precio = floatval($servicio->precio);
        $this->duracion = $servicio->duracion;
        $this->estado = $servicio->estado;
        $this->mostrar_feed = $servicio->mostrar_feed;
        $this->destacado = $servicio->destacado;

        // Cargar la imagen actual
        $this->imagen_actual = $servicio->imagen;

        $this->openModal();
    }

    public function borrar($id)
    {
        Servicio::find($id)->delete();
        session()->flash('message', 'Servicio eliminado exitosamente.');
        $this->emit('refreshDatatableServicios');
    }
    public function render()
    {
        return view('livewire.servicios');
    }
}
