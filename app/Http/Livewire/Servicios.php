<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Servicio;
use App\Models\Categoria;
use App\Models\Persona;
use App\Models\HorarioDisponibilidad;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

use Livewire\WithFileUploads;

class Servicios extends Component
{
    use WithFileUploads;

    public $buffer_tiempo, $anticipacion_minima, $anticipacion_maxima, $cancelacion_limite, $tipo_reserva, $servicios, $servicio_id, $imagen, $imagen_actual, $nombre, $descripcion,  $precio, $duracion, $busqueda, $categorias, $categoria_id;
    public $isOpen = 0;
    public $estado = 0;
    public $mostrar_feed = 0;
    public $destacado = 0;
    public $modoEdit = 0;
    public $loading = false;
    public $es_reservable = 0;
    public $servicio_copiar = '';
    public $servicios_con_horarios = [];
    public $empleadosDisponibles = []; // Lista para el select
    public $empleadosSeleccionados = []; // IDs de los empleados asignados al servicio
    public $empleadoSeleccionadoId = null; // Para el select individual

    // Nuevas propiedades para horarios
    public $dias_disponibles = [];
    public $horarios = []; // [dia => [['inicio' => 'HH:mm', 'fin' => 'HH:mm']]]
    public $dias_semana = [
        1 => 'Lunes',
        2 => 'Martes',
        3 => 'Miércoles',
        4 => 'Jueves',
        5 => 'Viernes',
        6 => 'Sábado',
        7 => 'Domingo'
    ];

    public $layout = 'sistema';

    protected $listeners = [
        'editarServicio' => 'editar',
        'openModal' => 'openModal'
    ];

    public function mount()
    {
        $this->categorias = $this->getCategorias();
        $this->loadServiciosConHorarios();
        $this->initializeHorarios();
        // Cargar empleados disponibles al montar el componente
        $this->loadEmpleadosDisponibles();
    }
    public function loadEmpleadosDisponibles()
    {
        $user = Auth::user();
        if ($user && $user->local->id) {
            $this->empleadosDisponibles = Persona::where('id_local', $user->local->id)
                ->where('tipo_persona', 'empleado')
                ->where('estado', 'Activo')
                ->get(['idpersona', 'nombre']);
        } else {
            $this->empleadosDisponibles = collect();
        }
    }
    public function loadServiciosConHorarios()
    {
        $this->servicios_con_horarios = Servicio::where('id_local', auth()->user()->local->id)
            ->where('tipo_reserva', 'turno_fijo')
            ->whereHas('horarios')
            ->get(['idservicio', 'nombre']);
    }

    // Aplicar horario del lunes a toda la semana
    public function aplicarHorarioSemana($diaBase)
    {
        if (!isset($this->horarios[$diaBase])) return;

        $horarioBase = $this->horarios[$diaBase];

        // Seleccionar todos los días
        $this->dias_disponibles = array_keys($this->dias_semana);

        // Aplicar el mismo horario a todos los días
        foreach ($this->dias_disponibles as $dia) {
            $this->horarios[$dia] = $horarioBase;
        }

        session()->flash('message', 'Horario aplicado a toda la semana');
    }

    // Copiar horario de otro servicio
    public function copiarHorarioServicio()
    {
        if (!$this->servicio_copiar) {
            return; // No hacer nada si no hay servicio seleccionado
        }

        $horarios = HorarioDisponibilidad::where('idservicio', $this->servicio_copiar)
            ->where('activo', true)
            ->get();

        // Limpiar horarios actuales
        $this->dias_disponibles = [];
        $this->horarios = [];

        // Cargar horarios del servicio seleccionado
        foreach ($horarios as $horario) {
            if (!in_array($horario->dia_semana, $this->dias_disponibles)) {
                $this->dias_disponibles[] = $horario->dia_semana;
            }

            if (!isset($this->horarios[$horario->dia_semana])) {
                $this->horarios[$horario->dia_semana] = [
                    ['inicio' => '', 'fin' => ''],
                    ['inicio' => '', 'fin' => '']
                ];
            }
            // AGREGAR ESTA LÍNEA - Inicializar estructura de horarios
            $this->updatedDiasDisponibles();

            // Buscar slot disponible
            for ($i = 0; $i < 2; $i++) {
                if (empty($this->horarios[$horario->dia_semana][$i]['inicio'])) {
                    $this->horarios[$horario->dia_semana][$i] = [
                        'inicio' => $horario->hora_inicio,
                        'fin' => $horario->hora_fin
                    ];
                    break;
                }
            }
        }

        $this->servicio_copiar = '';
        // session()->flash('message', 'Horarios copiados exitosamente');
    }

    private function initializeHorarios()
    {
        // Inicializar array de horarios vacío para cada día
        foreach ($this->dias_semana as $dia => $nombre) {
            $this->horarios[$dia] = [
                ['inicio' => '', 'fin' => '']
            ];
        }
    }

    public function getCategorias()
    {
        $idLocal = Auth::user()->local->id;
        return Categoria::where('id_local', $idLocal)->pluck('nombre', 'id_categoria')->toArray();
    }

    public function crear()
    {
        $this->resetInputFields();
        // ... tu código existente en crear()
        $this->empleadosSeleccionados = []; // Limpiar la lista de seleccionados
        $this->empleadoSeleccionadoId = null;
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
        $this->estado = 0;
        $this->mostrar_feed = 0;
        $this->destacado = 0;
        $this->servicio_id = '';
        $this->precio = '';
        $this->duracion = '';
        $this->imagen = '';
        $this->tipo_reserva = '';
        $this->buffer_tiempo = '';
        $this->anticipacion_minima = '';
        $this->anticipacion_maxima = '';
        $this->cancelacion_limite = '';
        $this->es_reservable = 0;

        // Reset horarios
        $this->dias_disponibles = [];
        $this->initializeHorarios();
    }

    // Métodos para gestión de horarios
    public function toggleDia($dia)
    {
        if (in_array($dia, $this->dias_disponibles)) {
            $this->dias_disponibles = array_diff($this->dias_disponibles, [$dia]);
        } else {
            $this->dias_disponibles[] = $dia;
        }
    }

    public function addHorarioBlock($dia)
    {
        $this->horarios[$dia][] = ['inicio' => '', 'fin' => ''];
    }

    public function removeHorarioBlock($dia, $index)
    {
        if (count($this->horarios[$dia]) > 1) {
            unset($this->horarios[$dia][$index]);
            $this->horarios[$dia] = array_values($this->horarios[$dia]);
        }
    }
    public function updatedDiasDisponibles()
    {
        // Inicializar horarios para días seleccionados
        foreach ($this->dias_disponibles as $dia) {
            if (!isset($this->horarios[$dia])) {
                $this->horarios[$dia] = [
                    ['inicio' => '', 'fin' => ''],
                    ['inicio' => '', 'fin' => '']
                ];
            }
        }

        // Limpiar horarios de días no seleccionados
        foreach ($this->horarios as $dia => $horarios) {
            if (!in_array($dia, $this->dias_disponibles)) {
                unset($this->horarios[$dia]);
            }
        }
    }

    public function updatedTipoReserva()
    {
        // Limpiar horarios si no es turno fijo
        if ($this->tipo_reserva !== 'turno_fijo') {
            $this->dias_disponibles = [];
            $this->horarios = [];
        } else {
            $this->loadServiciosConHorarios(); // Agregar esta línea
        }
    }

    private function validateHorarios()
    {
        if ($this->tipo_reserva !== 'turno_fijo') {
            return true;
        }

        if (empty($this->dias_disponibles)) {
            throw new \Exception('Debes seleccionar al menos un día disponible.');
        }

        foreach ($this->dias_disponibles as $dia) {
            if (!isset($this->horarios[$dia])) continue;

            foreach ($this->horarios[$dia] as $index => $horario) {
                if (empty($horario['inicio']) || empty($horario['fin'])) {
                    throw new \Exception("Completa todos los horarios para {$this->dias_semana[$dia]}.");
                }

                if ($horario['inicio'] >= $horario['fin']) {
                    throw new \Exception("La hora de fin debe ser mayor que la de inicio en {$this->dias_semana[$dia]}.");
                }

                // Validar solapamientos en el mismo día
                foreach ($this->horarios[$dia] as $otherIndex => $otroHorario) {
                    if ($index !== $otherIndex && !empty($otroHorario['inicio']) && !empty($otroHorario['fin'])) {
                        if (($horario['inicio'] < $otroHorario['fin']) && ($horario['fin'] > $otroHorario['inicio'])) {
                            throw new \Exception("Los horarios se solapan en {$this->dias_semana[$dia]}.");
                        }
                    }
                }
            }
        }

        return true;
    }

    private function saveHorarios($servicioId)
    {
        if ($this->tipo_reserva !== 'turno_fijo') {
            return;
        }

        // Eliminar horarios existentes
        HorarioDisponibilidad::where('idservicio', $servicioId)->delete();

        // Guardar nuevos horarios
        foreach ($this->dias_disponibles as $dia) {
            if (!isset($this->horarios[$dia])) continue;

            foreach ($this->horarios[$dia] as $horario) {
                if (!empty($horario['inicio']) && !empty($horario['fin'])) {
                    HorarioDisponibilidad::create([
                        'id_local' => auth()->user()->local->id,
                        'idservicio' => $servicioId,
                        'dia_semana' => $dia,
                        'hora_inicio' => $horario['inicio'],
                        'hora_fin' => $horario['fin'],
                        'activo' => true
                    ]);
                }
            }
        }
    }

    private function loadHorarios($servicioId)
    {
        $horarios = HorarioDisponibilidad::where('idservicio', $servicioId)->get();

        $this->dias_disponibles = [];
        $this->initializeHorarios();

        foreach ($horarios as $horario) {
            $dia = $horario->dia_semana;

            if (!in_array($dia, $this->dias_disponibles)) {
                $this->dias_disponibles[] = $dia;
                $this->horarios[$dia] = [];
            }

            $this->horarios[$dia][] = [
                'inicio' => substr($horario->hora_inicio, 0, 5), // HH:mm
                'fin' => substr($horario->hora_fin, 0, 5)
            ];
        }

        // Asegurar que cada día tenga al menos un bloque vacío
        foreach ($this->dias_disponibles as $dia) {
            if (empty($this->horarios[$dia])) {
                $this->horarios[$dia] = [['inicio' => '', 'fin' => '']];
            }
        }
    }

    public function guardar()
    {
        $this->loading = true;

        // Forzar a enteros antes de validar y guardar
        $this->estado = (int) $this->estado;
        $this->mostrar_feed = (int) $this->mostrar_feed;
        $this->destacado = (int) $this->destacado;

        try {
            $this->validate([
                'nombre' => 'required',
                'descripcion' => 'required',
                'estado' => 'boolean',
                'destacado' => 'boolean',
                'mostrar_feed' => 'boolean',
                'imagen' => 'nullable|image|max:2048',
                'tipo_reserva' => 'required|in:sin_reserva,coordinacion,turno_fijo',
                'duracion' => 'required_if:tipo_reserva,turno_fijo|nullable|numeric|min:1',
                'empleadosSeleccionados' => 'array',
            ]);

            // Validar horarios si es necesario
            $this->validateHorarios();

            $idLocal = auth()->user()->local->id;
            $nombreArchivo = null;

            if ($this->imagen) {
                $nombreLimpio = str_replace(' ', '_', strtolower($this->nombre));
                $extension = $this->imagen->getClientOriginalExtension();

                $idServicio = $this->servicio_id ?? uniqid();
                $nombreArchivo = "{$nombreLimpio}_{$idServicio}.{$extension}";

                $rutaCarpeta = "locales/{$idLocal}/servicios";
                // $rutaCarpeta = "public/locales/{$idLocal}/servicios";

                if (!Storage::exists($rutaCarpeta)) {
                    Storage::makeDirectory($rutaCarpeta);
                }

                $this->imagen->storeAs($rutaCarpeta, $nombreArchivo);
            }

            DB::transaction(function () use ($idLocal, $nombreArchivo) {
                $servicio = Servicio::updateOrCreate(['idservicio' => $this->servicio_id], [
                    'idcategoria' => $this->categoria_id,
                    'nombre' => $this->nombre,
                    'descripcion' => $this->descripcion,
                    'precio' => $this->precio,
                    'duracion' => $this->duracion,
                    'buffer_tiempo' => $this->buffer_tiempo,
                    'anticipacion_minima' => $this->anticipacion_minima,
                    'anticipacion_maxima' => $this->anticipacion_maxima,
                    'cancelacion_limite' => $this->cancelacion_limite,
                    'es_reservable' => $this->es_reservable,
                    'tipo_reserva' => $this->tipo_reserva,
                    'estado' => $this->estado,
                    'mostrar_feed' => $this->mostrar_feed,
                    'destacado' => $this->destacado,
                    'imagen' => $nombreArchivo
                        ? "locales/{$idLocal}/servicios/{$nombreArchivo}"
                        : ($this->imagen_actual ?? null),
                    'id_local' => $idLocal
                ]);

                // Guardar horarios
                $this->saveHorarios($servicio->idservicio);
                $servicio->empleados()->sync($this->empleadosSeleccionados);
            });

            session()->flash(
                'message',
                $this->servicio_id ? 'Servicio actualizado exitosamente.' : 'Servicio creado exitosamente.'
            );

            $this->emit('refreshDatatableServicios');
            $this->closeModal();
            $this->resetInputFields();
        } catch (\Exception $e) {
            Log::error("Error al guardar el servicio: " . $e->getMessage());
            session()->flash('error', $e->getMessage());
        } finally {
            $this->loading = false;
        }
    }
    public function addEmpleado()
    {
        if ($this->empleadoSeleccionadoId && !in_array($this->empleadoSeleccionadoId, $this->empleadosSeleccionados)) {
            $this->empleadosSeleccionados[] = $this->empleadoSeleccionadoId;
            $this->empleadoSeleccionadoId = null; // Resetear la selección
        }
    }

    public function removeEmpleado($empleadoId)
    {
        $this->empleadosSeleccionados = array_diff($this->empleadosSeleccionados, [$empleadoId]);
    }

    public function editar($id)
    {
        $this->modoEdit = true;
        $servicio = Servicio::with('empleados')->findOrFail($id);
        $this->servicio_id = $id;
        $this->categoria_id = $servicio->idcategoria;
        $this->nombre = $servicio->nombre;
        $this->descripcion = $servicio->descripcion;
        $this->precio = floatval($servicio->precio);
        $this->duracion = $servicio->duracion;
        $this->buffer_tiempo = $servicio->buffer_tiempo;
        $this->anticipacion_minima = $servicio->anticipacion_minima;
        $this->anticipacion_maxima = $servicio->anticipacion_maxima;
        $this->cancelacion_limite = $servicio->cancelacion_limite;
        $this->es_reservable = $servicio->es_reservable;
        $this->tipo_reserva = $servicio->tipo_reserva;
        $this->estado = $servicio->estado;
        $this->mostrar_feed = $servicio->mostrar_feed;
        $this->destacado = $servicio->destacado;
        $this->imagen_actual = $servicio->imagen;

        // Cargar horarios existentes
        $this->loadHorarios($id);

        $this->openModal(); // Cargar los IDs de los empleados asignados
        $this->empleadosSeleccionados = $servicio->empleados->pluck('idpersona')->toArray();

        $this->openModal();
    }

    public function borrar($id)
    {
        DB::transaction(function () use ($id) {
            // Eliminar horarios asociados
            HorarioDisponibilidad::where('idservicio', $id)->delete();
            // Eliminar servicio
            Servicio::find($id)->delete();
        });

        session()->flash('message', 'Servicio eliminado exitosamente.');
        $this->emit('refreshDatatableServicios');
    }

    public function render()
    {
        return view('livewire.servicios');
    }
}
