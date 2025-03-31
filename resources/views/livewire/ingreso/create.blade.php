<div class="fixed inset-0 z-50 overflow-y-auto">
    <div class="flex justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-6xl sm:w-full"
            role="dialog" aria-modal="true" aria-labelledby="modal-headline">
            {{-- <form> --}}
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <!-- Buscador de Cliente -->
                <div class="mb-7">
                    <input wire:model.debounce.300ms="searchCliente" autofocus type="text"
                        class="block w-full border border-gray-300 rounded-md py-2 px-3 text-black cursor-pointer"
                        placeholder="Buscar un cliente">
                    @if ($searchCliente)
                        <div class="absolute z-50 bg-white w-full mt-1 rounded-md shadow-lg">
                            @foreach ($personas as $opcion)
                                <div wire:click="agregarCliente({{ $opcion['idpersona'] }})"
                                    class="py-2 px-3 cursor-pointer hover:bg-gray-100">
                                    {{ $opcion['nombre'] }}
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>


                @if ($clienteSeleccionado)
                    @livewire('ingreso.carga-pagos', ['idpersona' => $idpersona])
                @endif
            </div>
             <!-- Pie del modal -->
         <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
            <button wire:click="closeModal"
                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                Cerrar
            </button>
        </div>

            {{-- </form> --}}
        </div>

    </div>
</div>
