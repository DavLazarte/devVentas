<div class="fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 py-6 text-center sm:block sm:p-0">

        <div class="fixed inset-0 transition-opacity">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:align-middle sm:max-w-4xl sm:w-full sm:my-8"
            role="dialog" aria-modal="true" aria-labelledby="modal-headline">
            <form>
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="mb-4">
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

                    @if ($clienteSeleccionado)
                        <div class="mb-4">
                            <label for="idpersona" class="block text-sm font-medium text-gray-700">Cliente</label>
                            <input type="text" id="idpersona" wire:model="nombre_cliente"
                                class="block w-full border border-gray-300 rounded-md py-2 px-3 text-black mt-1">
                        </div>

                        <div class="overflow-x-auto mb-4">
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
                                            <td class="py-2 px-4 border-b">{{ $venta->created_at->format('d/m/Y') }}</td>
                                            <td class="py-2 px-4 border-b">{{ $venta->total_venta }}</td>
                                            <td class="py-2 px-4 border-b">{{ $venta->saldo }}</td>
                                            <td class="py-2 px-4 border-b">
                                                <input type="number" wire:model="saldos.{{ $venta->id }}"
                                                    class="border rounded px-2 py-1 w-full">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="4">
                                                <div x-data="{ open: false }">
                                                    <div class="border-t">
                                                        <button type="button" @click="open = ! open"
                                                                class="w-full py-2 px-4 text-left text-gray-700 hover:bg-gray-100 focus:outline-none">
                                                            Ver detalles de venta
                                                        </button>
                                                    </div>
                                                    <div x-show="open" x-transition class="p-4 bg-gray-50">
                                                        @include('livewire.ingreso.detalle-venta', ['venta' => $venta])
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

                    <div class="mb-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="monto" class="block text-sm font-bold text-gray-700">Monto:</label>
                            <input type="number" id="monto" wire:model="monto" wire:change="distribuirPago"
                                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        </div>
                        <div>
                            <label for="saldo" class="block text-sm font-bold text-gray-700">Nuevo Saldo:</label>
                            <input type="number" id="saldo" wire:model="saldo"
                                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="descripcion" class="block text-sm font-bold text-gray-700">Descripción:</label>
                        <input type="text" id="descripcion" wire:model="descripcion"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>

                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <span class="flex w-full rounded-md shadow-sm sm:ml-3 sm:w-auto">
                            <button wire:click.prevent="guardar()" type="button"
                                class="inline-flex justify-center w-full rounded-md border border-transparent px-4 py-2 bg-purple-600 text-base leading-6 font-medium text-white shadow-sm hover:bg-purple-800 focus:outline-none focus:border-purple-700 focus:shadow-outline-purple transition ease-in-out duration-150 sm:text-sm sm:leading-5">
                                Guardar
                            </button>
                        </span>
                        <span class="flex w-full rounded-md shadow-sm sm:ml-3 sm:w-auto">
                            <button wire:click="closeModal()" type="button"
                                class="inline-flex justify-center w-full rounded-md border border-gray-300 px-4 py-2 bg-gray-200 text-base leading-6 font-medium text-gray-700 shadow-sm hover:text-gray-500 focus:outline-none focus:border-gray-300 focus:shadow-outline-gray transition ease-in-out duration-150 sm:text-sm sm:leading-5">
                                Cancelar
                            </button>
                        </span>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
    