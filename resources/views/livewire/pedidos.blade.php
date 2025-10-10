<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px6 lg:px-8">
        <h1 class="text-2xl font-bold text-gray-900">Gestión</h1>
        @if (session()->has('message'))
            <div class="mt-4 bg-purple-100 border-l-4 border-purple-500 text-purple-700 p-4" role="alert">
                <p class="font-bold">Notificación</p>
                <p>{{ session('message') }}</p>
            </div>
        @endif
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg px-4 py-4">
            {{-- Your existing button and modal code --}}
            <div class="flex justify-end space-x-2 mb-4">
                <button wire:click="$set('vista', 'lista')"
                    class="px-4 py-2 rounded-lg font-semibold text-white 
                           {{ $vista === 'lista' ? 'bg-purple-600 hover:bg-purple-700' : 'bg-gray-500 hover:bg-gray-600' }}">
                    Lista
                </button>

                @if ($tipoPedido === 'servicio')
                    <button wire:click="$set('vista', 'calendario')"
                        class="px-4 py-2 rounded-lg font-semibold text-white 
                               {{ $vista === 'calendario' ? 'bg-purple-600 hover:bg-purple-700' : 'bg-gray-500 hover:bg-gray-600' }}">
                        Calendario
                    </button>
                @endif
            </div>

            @if ($vista === 'lista')
                {{-- @livewire('pedido-table' ) --}}
                <livewire:pedido-table />
            @else
                {{-- Calendar View --}}
                <div class="mt-4">
                    <div wire:ignore id='calendar'></div>
                </div>
            @endif


            @if ($isDetailOpen)
                <div x-data="{ open: @entangle('isDetailOpen') }" x-show="open" x-on:keydown.escape.window="open = false"
                    class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
                    <div
                        class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                        <div x-show="open" class="fixed inset-0 transition-opacity" aria-hidden="true">
                            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                        </div>

                        <div
                            class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-7xl sm:w-full">
                            @include('livewire.pedido.show')
                        </div>
                    </div>
                </div>
            @endif

            @if ($isOpen)
                <div x-data="{ open: @entangle('isOpen') }" x-show="open" x-on:keydown.escape.window="open = false"
                    class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
                    <div
                        class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                        <div x-show="open" class="fixed inset-0 transition-opacity" aria-hidden="true">
                            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                        </div>

                        <div
                            class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-7xl sm:w-full">
                            @include('livewire.pedido.editar')
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </div>
</div>

@push('js')
    <script>
        let calendar = null;

        function initCalendar() {
            // Verificar que FullCalendar esté disponible
            if (typeof FullCalendar === 'undefined') {
                console.error('FullCalendar no está cargado');
                return;
            }

            var calendarEl = document.getElementById('calendar');
            if (!calendarEl) {
                console.error('Elemento calendar no encontrado');
                return;
            }

            // Destruir calendario existente si existe
            if (calendar) {
                calendar.destroy();
            }

            calendar = new FullCalendar.Calendar(calendarEl, {
                locale: 'es',
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                buttonText: {
                    today: 'Hoy',
                    month: 'Mes',
                    week: 'Semana',
                    day: 'Día'
                },
                events: @json($reservas),
                eventClick: function(info) {
                    // Mostrar modal con opciones de acción
                    showEventActions(info.event.id, info.event.title);
                }
            });

            calendar.render();
        }

        // Función para mostrar las acciones del evento
        function showEventActions(eventId, eventTitle) {
            // Crear modal con las mismas acciones que la tabla
            const modal = `
            <div id="eventActionsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
                <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                    <div class="mt-3">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">${eventTitle}</h3>
                        <div class="flex space-x-2 justify-center">
                            <button onclick="verDetalle(${eventId})" 
                                class="flex items-center bg-green-500 hover:bg-green-600 text-white font-medium py-2 px-3 rounded-lg text-sm transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                Ver
                            </button>
                            <button onclick="editar(${eventId})" 
                                class="flex items-center bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-3 rounded-lg text-sm transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Estado
                            </button>
                            <button onclick="eliminar(${eventId})" 
                                class="flex items-center bg-red-500 hover:bg-red-600 text-white font-medium py-2 px-3 rounded-lg text-sm transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Eliminar
                            </button>
                        </div>
                        <div class="mt-4 flex justify-end">
                            <button onclick="closeEventModal()" 
                                class="bg-gray-500 hover:bg-gray-600 text-white font-medium py-2 px-4 rounded-lg text-sm transition-colors">
                                Cerrar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

            document.body.insertAdjacentHTML('beforeend', modal);
        }

        // Funciones para las acciones
        function verDetalle(id) {
            @this.call('verDetalle', id);
            closeEventModal();
        }

        function editar(id) {
            @this.call('editar', id);
            closeEventModal();
        }

        function eliminar(id) {
            if (confirm('¿Estás seguro de que quieres eliminar este pedido?')) {
                @this.call('eliminar', id);
            }
            closeEventModal();
        }

        function closeEventModal() {
            const modal = document.getElementById('eventActionsModal');
            if (modal) {
                modal.remove();
            }
        }

        // Inicializar cuando el DOM esté listo
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(initCalendar, 100);
        });

        // Escuchar cambios en Livewire
        Livewire.on('refreshCalendar', (events) => {
            if (calendar) {
                calendar.removeAllEvents();
                calendar.addEventSource(events);
            }
        });

        Livewire.on('initCalendar', () => {
            setTimeout(initCalendar, 200);
        });

        Livewire.on('$refresh', () => {
            setTimeout(initCalendar, 100);
        });
    </script>
@endpush
