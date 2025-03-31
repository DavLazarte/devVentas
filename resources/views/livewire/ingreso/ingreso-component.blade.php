<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg px-4 py-4">
            <div class="p-4">
                <div class="flex justify-between items-center">
                    <h1 class="text-2xl font-bold text-gray-900">Gestión de Pagos</h1>
                    <button wire:click="crear()"
                        class="flex items-center bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Cargar Pago
                    </button>
                </div>

                @if (session()->has('message'))
                    <div class="mt-4 bg-purple-100 border-l-4 border-purple-500 text-purple-700 p-4" role="alert">
                        <p class="font-bold">Notificación</p>
                        <p>{{ session('message') }}</p>
                    </div>
                @endif

                @if ($isOpen)
                    @include('livewire.ingreso.create')
                @endif

            </div>
            @livewire('ingreso.ingreso-table')

        </div>
    </div>
</div>

