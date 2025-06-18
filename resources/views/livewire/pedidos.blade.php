<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg px-4 py-4">
            <div class="p-4">
                <div class="flex justify-between items-center">
                    <h1 class="text-2xl font-bold text-gray-900">Gestión de Pedidos</h1>
                    {{-- <button wire:click="crear()"
                        class="flex items-center bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Crear
                    </button> --}}
                </div>

                @if (session()->has('message'))
                    <div class="mt-4 bg-purple-100 border-l-4 border-purple-500 text-purple-700 p-4" role="alert">
                        <p class="font-bold">Notificación</p>
                        <p>{{ session('message') }}</p>
                    </div>
                @endif

                @if ($isDetailOpen)
                    <div x-data="{ open: @entangle('isDetailOpen') }" 
                         x-show="open" 
                         x-on:keydown.escape.window="open = false"
                         class="fixed inset-0 z-50 overflow-y-auto"
                         style="display: none;">
                        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                            <div x-show="open" 
                                 class="fixed inset-0 transition-opacity" 
                                 aria-hidden="true">
                                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                            </div>

                            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-7xl sm:w-full">
                                @include('livewire.pedido.show')
                            </div>
                        </div>
                    </div>
                @endif

                @if ($isOpen)
                    <div x-data="{ open: @entangle('isOpen') }" 
                         x-show="open" 
                         x-on:keydown.escape.window="open = false"
                         class="fixed inset-0 z-50 overflow-y-auto"
                         style="display: none;">
                        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                            <div x-show="open" 
                                 class="fixed inset-0 transition-opacity" 
                                 aria-hidden="true">
                                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                            </div>

                            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-7xl sm:w-full">
                                @include('livewire.pedido.editar')
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            @livewire('pedido-table')
        </div>
    </div>
</div>
