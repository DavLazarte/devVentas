<div class="fixed inset-0 z-50 overflow-y-auto">
    <div class="flex justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">

        <div class="fixed inset-0 transition-opacity">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full"
            role="dialog" aria-modal="true" aria-labelledby="modal-headline">
            <form>
                <div class="bg-white px-4 pt-5 pb-4 sm:p-4 sm:pb-2">
                    <div class="mb-7">
                        <div class="gap-x-6 gap-y-8 ">
                            <input wire:model.debounce.300ms="searchCliente" type="text"
                                class="block w-full border border-gray-300 rounded-md py-2 px-3 text-black"
                                placeholder="Buscar un cliente">

                            @if ($searchCliente)
                                <div class="absolute z-50 bg-white w-full mt-1 rounded-md shadow-lg">
                                    @foreach ($personas as $opcion)
                                        <div wire:click="agregarCliente({{ $opcion['idpersona'] }})"
                                            class="py-2 px-3 cursor-pointer hover:bg-gray-100">
                                            {{ $opcion['nombre'] }}</div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="mt-4 mb-8">
                        @if ($clienteSeleccionado)
                            <div class="mt-4 mb-12">
                                <label for="idpersona"
                                    class="block text-sm font-medium leading-6 text-gray-900">Cliente</label>
                                <div class="relative mt-2 rounded-md shadow-sm">
                                    <input type="text" id="idpersona" wire:model="nombre_cliente"
                                        class="absolute z-50 bg-white w-full mt-1 rounded-md shadow-lg">
                                </div>
                            </div>

                            <div class="mt-8">
                                <table class="min-w-full bg-white border border-gray-300">
                                    <thead>
                                        <tr>
                                            <th class="py-2 px-4 border-b">Fecha</th>
                                            <th class="py-2 px-4 border-b">Total</th>
                                            <th class="py-2 px-4 border-b">Saldo antiguo</th>
                                            <th class="py-2 px-4 border-b">Saldo nuevo</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($ventasConSaldos as $venta)
                                            <tr>
                                                <td class="py-2 px-4 border-b">{{ $venta->created_at->format('d/m/Y') }}
                                                </td>
                                                <td class="py-2 px-4 border-b">{{ $venta->total_venta }}</td>
                                                <td class="py-2 px-4 border-b">{{ $venta->saldo }}</td>
                                                <td class="py-2 px-4 border-b">
                                                    <input type="number" wire:model="saldos.{{ $venta->id }}"
                                                        class=" border rounded px-2 py-1 w-full">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="4">
                                                    <div class="accordion" id="accordionVenta-{{ $venta->id }}">
                                                        <div class="accordion-item">
                                                            <h2 class="accordion-header" id="heading-{{ $venta->id }}">
                                                                <button class="accordion-button collapsed" type="button"
                                                                        data-bs-toggle="collapse"
                                                                        data-bs-target="#collapse-{{ $venta->id }}"
                                                                        aria-expanded="false"
                                                                        aria-controls="collapse-{{ $venta->id }}">
                                                                    Ver detalles de venta
                                                                </button>
                                                            </h2>
                                                            <div id="collapse-{{ $venta->id }}"
                                                                 class="accordion-collapse collapse"
                                                                 aria-labelledby="heading-{{ $venta->id }}"
                                                                 data-bs-parent="#accordionVenta-{{ $venta->id }}">
                                                                <div class="accordion-body">
                                                                    @include('livewire.ingreso.detalle-venta')
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>


                                <div class="mt-4">
                                    <p class="font-bold">Total de saldos: {{ $totalSaldos }}</p>
                                </div>
                            </div>

                        @endif
                    </div>



                    <div class="mb-4 grid grid-cols-12">
                        <div class="col-span-12 sm:col-span-6 m-1">
                            <label for="monto" class="block text-gray-700 text-sm font-bold mb-2">Monto:</label>
                            <input type="number"
                                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                id="monto" wire:model="monto" wire:change="distribuirPago">
                        </div>
                        <div class="col-span-12 sm:col-span-6 m-1">
                            <label for="saldo" class="block text-gray-700 text-sm font-bold mb-2"> Nuevo
                                Saldo:</label>
                            <input type="number"
                                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                id="saldo" wire:model="saldo">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="descripcion" class="block text-gray-700 text-sm font-bold mb-2">Descripción:</label>
                        <input type="text"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            id="descripcion" wire:model="descripcion">
                    </div>


                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <span class="flex w-full rounded-md shadow-sm sm:ml-3 sm:w-auto">
                            <button wire:click.prevent="guardar()" type="button"
                                class="inline-flex justify-center w-full rounded-md border border-transparent px-4 py-2 bg-purple-600 text-base leading-6 font-medium text-white shadow-sm hover:bg-purple-800 focus:outline-none focus:border-green-700 focus:shadow-outline-green transition ease-in-out duration-150 sm:text-sm sm:leading-5">Guardar</button>
                        </span>

                        <span class="flex w-full rounded-md shadow-sm sm:ml-3 sm:w-auto">
                            <button wire:click="closeModal()" type="button"
                                class="inline-flex justify-center w-full rounded-md border border-gray-300 px-4 py-2 bg-gray-200 text-base leading-6 font-medium text-gray-700 shadow-sm hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:shadow-outline-blue transition ease-in-out duration-150 sm:text-sm sm:leading-5">Cancelar</button>
                        </span>
                    </div>

                </div>
            </form>
        </div>


    </div>
</div>
