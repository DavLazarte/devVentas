<div class="fixed inset-0 z-50 overflow-hidden">
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-black bg-opacity-50" wire:click="closeModal"></div>
    <!-- Drawer -->
    <div class="absolute inset-y-0 right-0 w-full sm:w-[500px] sm:max-w-lg bg-white shadow-xl flex flex-col">
        <!-- Header -->
        <div class="sticky top-0 bg-white border-b border-gray-200 p-4 flex justify-between items-center z-10">
            <h2 class="text-lg font-semibold text-gray-900">{{ $modoEdit ? 'Editar Servicio' : 'Crear Servicio' }}</h2>
            <button wire:click="closeModal" class="text-gray-500 hover:text-gray-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <!-- Formulario -->
        <form class="flex flex-col h-full overflow-hidden" wire:submit.prevent="guardar">
            <div class="flex-1 overflow-y-auto p-4 space-y-4">
                @if (session()->has('error'))
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4" role="alert">
                        <p class="font-bold">ERROR</p>
                        <p>{{ session('error') }}</p>
                    </div>
                @endif
                <div>
                    <label for="categoria_id" class="block text-gray-700 text-sm font-bold mb-2">Categoría:</label>
                    <select wire:model="categoria_id" id="categoria_id" name="categoria_id"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 sm:text-sm">
                        <option value="">Selecciona una categoría</option>
                        @foreach ($categorias as $id => $nombre)
                            <option value="{{ $id }}" @if ($categoria_id == $id) selected @endif>
                                {{ $nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="nombre" class="block text-gray-700 text-sm font-bold mb-2">Nombre:</label>
                    <input type="text" id="nombre" wire:model="nombre"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 sm:text-sm">
                </div>
                <div>
                    <label for="descripcion" class="block text-gray-700 text-sm font-bold mb-2">Descripción:</label>
                    <input type="text" id="descripcion" wire:model="descripcion"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 sm:text-sm">
                </div>
                <div>
                    <label for="imagen" class="block text-sm font-medium text-gray-700">Imagen para el servicio
                        :</label>
                    <input type="file" id="imagen" wire:model="imagen"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    @error('imagen')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                    <span wire:loading wire:target="imagen" class="text-sm text-gray-600">Cargando imagen...</span>
                </div>
                @if ($imagen_actual)
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Imagen Actual:</label>
                        <img src="{{ asset('storage/' . $imagen_actual) }}" class="w-32 h-32 object-cover mt-2">
                    </div>
                @endif
                <div>
                    <label for="precio" class="block text-gray-700 text-sm font-bold mb-2">Precio</label>
                    <input type="number" id="precio" wire:model="precio"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 sm:text-sm">
                </div>
                <div>
                    <label for="tipo_reserva" class="block text-gray-700 text-sm font-bold mb-2">Reserva:</label>
                    <select wire:model="tipo_reserva" id="tipo_reserva" name="tipo_reserva"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 sm:text-sm">
                        <option value="">Selecciona un tipo de reserva</option>
                        <option value="sin_reserva">No necesita reservar</option>
                        <option value="coordinacion">A Coordinar</option>
                        <option value="turno_fijo">Con Turno</option>
                    </select>
                </div>

                <!-- Sección de configuración de horarios -->
                @if ($tipo_reserva === 'turno_fijo')
                    <div class="mt-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-800">Configuración de Horarios</h3>

                            @if ($servicios_con_horarios && count($servicios_con_horarios) > 0)
                                <div class="flex items-center space-x-2">
                                    <select wire:model="servicio_copiar" class="text-sm rounded border-gray-300">
                                        <option value="">Copiar horario de otro servicio</option>
                                        @foreach ($servicios_con_horarios as $servicio)
                                            <option value="{{ $servicio->idservicio }}">{{ $servicio->nombre }}</option>
                                        @endforeach
                                    </select>

                                    <button type="button" wire:click="copiarHorarioServicio"
                                        class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded hover:bg-green-200 disabled:opacity-50">
                                        Copiar
                                    </button>
                                </div>
                            @endif
                        </div>

                        @foreach ($dias_semana as $numero_dia => $nombre_dia)
                            <div class="mb-4 p-3 bg-white rounded border">
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex items-center">
                                        <input type="checkbox" wire:model="dias_disponibles" value="{{ $numero_dia }}"
                                            id="dia_{{ $numero_dia }}"
                                            class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded">
                                        <label for="dia_{{ $numero_dia }}"
                                            class="ml-2 text-sm font-medium text-gray-900">
                                            {{ $nombre_dia }}
                                        </label>
                                    </div>
                                </div>

                                @if (in_array($numero_dia, $dias_disponibles))
                                    <div class="ml-6 space-y-3">
                                        @if ($numero_dia == 1)
                                            <!-- Solo mostrar en Lunes -->
                                            <div class="mb-3">
                                                <button type="button"
                                                    wire:click="aplicarHorarioSemana({{ $numero_dia }})"
                                                    class="text-xs bg-purple-100 text-purple-700 px-2 py-1 rounded hover:bg-purple-200">
                                                    📅 Aplicar este horario a toda la semana
                                                </button>
                                            </div>
                                        @endif
                                        <!-- Turno 1 -->
                                        <div class="grid grid-cols-2 gap-3">
                                            <div>
                                                <label class="block text-xs font-medium text-gray-700 mb-1">Turno 1 -
                                                    Inicio</label>
                                                <input type="time"
                                                    wire:model="horarios.{{ $numero_dia }}.0.inicio"
                                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 sm:text-sm">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-700 mb-1">Turno 1 -
                                                    Fin</label>
                                                <input type="time" wire:model="horarios.{{ $numero_dia }}.0.fin"
                                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 sm:text-sm">
                                            </div>
                                        </div>

                                        <!-- Turno 2 -->
                                        <div class="grid grid-cols-2 gap-3">
                                            <div>
                                                <label class="block text-xs font-medium text-gray-700 mb-1">Turno 2 -
                                                    Inicio</label>
                                                <input type="time"
                                                    wire:model="horarios.{{ $numero_dia }}.1.inicio"
                                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 sm:text-sm">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-700 mb-1">Turno 2 -
                                                    Fin</label>
                                                <input type="time" wire:model="horarios.{{ $numero_dia }}.1.fin"
                                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 sm:text-sm">
                                            </div>
                                        </div>

                                        <div class="text-xs text-gray-500 mt-2">
                                            <span class="font-medium">Nota:</span> El segundo turno es opcional. Si no
                                            necesitas dos turnos, deja los campos del turno 2 vacíos.
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach

                        @if (count($dias_disponibles) > 0)
                            <div class="mt-4 p-3 bg-blue-50 rounded border border-blue-200">
                                <div class="flex items-center">
                                    <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                    <p class="ml-2 text-sm text-blue-700">
                                        Selecciona los días y configura los horarios en que el servicio estará
                                        disponible para reservas.
                                    </p>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif
                <div>
                    <label for="duracion" class="block text-gray-700 text-sm font-bold mb-2">Duración en
                        minutos:</label>
                    <input type="text" id="duracion" wire:model="duracion"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 sm:text-sm">
                </div>
                <div class="col-span-full">
                    <label class="block text-sm font-medium text-gray-700">Asignar Empleados</label>
                    <div class="mt-1">
                        <select wire:model="empleadoSeleccionadoId" wire:change="addEmpleado"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <option value="">Selecciona un empleado...</option>
                            @foreach ($empleadosDisponibles as $empleado)
                                @if (!in_array($empleado->idpersona, $empleadosSeleccionados))
                                    <option value="{{ $empleado->idpersona }}">{{ $empleado->nombre }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <p class="mt-2 text-sm text-gray-500">
                        Los empleados que selecciones aquí estarán disponibles para este servicio.
                    </p>

                    {{-- Tags de empleados seleccionados --}}
                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach ($empleadosSeleccionados as $empleadoId)
                            @php
                                $empleado = $empleadosDisponibles->where('idpersona', $empleadoId)->first();
                            @endphp
                            @if ($empleado)
                                <span
                                    class="inline-flex items-center rounded-full bg-purple-100 px-3 py-1 text-sm font-semibold text-purple-800">
                                    {{ $empleado->nombre }}
                                    <button wire:click="removeEmpleado({{ $empleado->idpersona }})" type="button"
                                        class="ml-1 -mr-0.5 h-4 w-4 rounded-full text-purple-600 hover:text-purple-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </span>
                            @endif
                        @endforeach
                    </div>
                </div>
                <div>
                    <label for="buffer_tiempo" class="block text-gray-700 text-sm font-bold mb-2">Tiempo entre
                        Turnos:</label>
                    <input type="text" id="buffer_tiempo" wire:model="buffer_tiempo"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 sm:text-sm">
                </div>
                <div>
                    <label for="anticipacion_minima" class="block text-gray-700 text-sm font-bold mb-2">Anticipación
                        minima:</label>
                    <input type="text" id="anticipacion_minima" wire:model="anticipacion_minima"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 sm:text-sm">
                </div>
                <div>
                    <label for="anticipacion_maxima" class="block text-gray-700 text-sm font-bold mb-2">Anticipación
                        maxima:</label>
                    <input type="text" id="anticipacion_maxima" wire:model="anticipacion_maxima"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 sm:text-sm">
                </div>
                <div>
                    <label for="cancelacion_limite" class="block text-gray-700 text-sm font-bold mb-2">Limita para
                        Cancelar:</label>
                    <input type="text" id="cancelacion_limite" wire:model="cancelacion_limite"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 sm:text-sm">
                </div>
                <div>
                    <label for="es_reservable" class="block text-gray-700 text-sm font-bold mb-2">Disponible para
                        Reserva:</label>
                    <div class="flex items-center">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" id="es_reservable" wire:model="es_reservable"
                                name="es_reservable" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-purple-600"></div>
                            <span
                                class="ml-3 text-sm font-medium text-gray-700">{{ $es_reservable == 1 ? 'Activo' : 'Inactivo' }}</span>
                        </label>
                    </div>
                </div>
                <div>
                    <label for="estado" class="block text-gray-700 text-sm font-bold mb-2">Estado:</label>
                    <div class="flex items-center">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" id="estado" wire:model="estado" name="estado"
                                class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-purple-600"></div>
                            <span
                                class="ml-3 text-sm font-medium text-gray-700">{{ $estado == 1 ? 'Activo' : 'Inactivo' }}</span>
                        </label>
                    </div>
                </div>
                <div>
                    <label for="destacado" class="block text-gray-700 text-sm font-bold mb-2">Destacado:</label>
                    <div class="flex items-center">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" id="destacado" wire:model="destacado" name="destacado"
                                class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-purple-600"></div>
                            <span
                                class="ml-3 text-sm font-medium text-gray-700">{{ $destacado == 1 ? 'Si' : 'No' }}</span>
                        </label>
                    </div>
                </div>
                <div>
                    <label for="mostrar_feed" class="block text-gray-700 text-sm font-bold mb-2">Catalogo:</label>
                    <div class="flex items-center">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" id="mostrar_feed" wire:model="mostrar_feed" name="mostrar_feed"
                                class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-purple-600"></div>
                            <span
                                class="ml-3 text-sm font-medium text-gray-700">{{ $mostrar_feed == 1 ? 'Si' : 'No' }}</span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="bg-white border-t border-gray-200 p-4 flex space-x-2 shrink-0">
                <button wire:click.prevent="guardar()" type="button"
                    class="flex-1 inline-flex justify-center rounded-md border border-transparent px-4 py-2 bg-purple-600 text-base leading-6 font-medium text-white shadow-sm hover:bg-purple-800 focus:outline-none focus:border-purple-700 focus:shadow-outline-purple transition ease-in-out duration-150 sm:text-sm sm:leading-5"
                    wire:loading.attr="disabled" wire:loading.class="opacity-50" wire:target="guardar, imagen">
                    <span wire:loading.remove wire:target="guardar">Guardar</span>
                    <span wire:loading wire:target="guardar">Guardando...</span>
                </button>
                <button wire:click="closeModal()" type="button"
                    class="flex-1 inline-flex justify-center rounded-md border border-gray-300 px-4 py-2 bg-gray-200 text-base leading-6 font-medium text-gray-700 shadow-sm hover:text-gray-500 focus:outline-none focus:border-gray-300 focus:shadow-outline-gray transition ease-in-out duration-150 sm:text-sm sm:leading-5">
                    Cancelar
                </button>
            </div>
        </form>
    </div>
</div>
