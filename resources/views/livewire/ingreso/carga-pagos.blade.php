<div>
    <form>
        <!-- Información del Cliente y Ventas -->
        <div class="mb-8">
            <!-- Sección de Montos y Descripción -->
            <div class="bg-gray-100 p-4 rounded-md mb-4">
                <h2 class="text-lg font-bold text-gray-700 mb-2">Detalles del Pago de {{$nombre_cliente}}</h2>
                <p class="text-sm text-gray-600 mb-4">Ingresar el monto total y el sistema redistribuye automáticamente
                    en los saldos.</p>
                <div class="grid grid-cols-12 gap-4">
                    <div class="col-span-12 sm:col-span-6">
                        <label for="monto" class="block text-gray-700 text-sm font-bold mb-2">Monto:</label>
                        <input type="number" id="monto" wire:model="monto" wire:change="distribuirPago"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <label for="descripcion" class="block text-gray-700 text-sm font-bold mb-2">Descripción:</label>
                        <input type="text" id="descripcion" wire:model="descripcion"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>
                </div>
            </div>

            <!-- Total de Saldos -->
            <div class="bg-gray-100 p-4 rounded-md mb-4">
                <div class="col-span-12 sm:col-span-6">
                    <label for="saldo" class="block text-gray-700 text-sm font-bold mb-2">Nuevo Saldo Total:</label>
                    <input type="number" id="saldo" wire:model="saldo"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
            </div>

            <!-- Sección de Ventas con Saldo -->
            <h2 class="text-lg font-bold text-gray-700 mb-2">Ventas con Saldo</h2>
            <hr class="mb-4 border-gray-300">
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border border-gray-300">
                    <thead class="bg-gray-200">
                        <tr>
                            <th class="py-2 px-4 border-b text-left text-gray-600">Fecha</th>
                            <th class="py-2 px-4 border-b text-left text-gray-600">Total</th>
                            <th class="py-2 px-4 border-b text-left text-gray-600">Saldo antiguo</th>
                            <th class="py-2 px-4 border-b text-left text-gray-600">Saldo nuevo</th>
                            <th class="py-2 px-4 border-b text-left text-gray-600">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(!is_null($ventasConSaldos) && $ventasConSaldos->isNotEmpty())
                            @foreach ($ventasConSaldos as $venta)
                                <tr>
                                    <td class="py-2 px-4 border-b">{{ $venta->created_at->format('d/m/Y') }}</td>
                                    <td class="py-2 px-4 border-b">{{ $venta->total_venta }}</td>
                                    <td class="py-2 px-4 border-b">{{ $venta->saldo }}</td>
                                    <td class="py-2 px-4 border-b">
                                        <input type="number" wire:model="saldos.{{ $venta->id }}"
                                            class="border rounded px-2 py-1 w-full">
                                    </td>
                                    <td class="py-2 px-4 border-b">
                                        <button type="button" wire:click="toggleDetail({{ $venta->id }})"
                                            class="bg-blue-500 text-white rounded px-2 py-1">
                                            Ver Detalle
                                        </button>
                                    </td>
                                </tr>
                                @if (in_array($venta->id, $detallesVisibles))
                                    <tr>
                                        <td colspan="5" class="px-4 py-2">
                                            @include('livewire.ingreso.detallle-venta', ['venta' => $venta])
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        @else
                            <tr>
                                <td colspan="5" class="py-2 px-4 text-center text-gray-500">
                                    No hay ventas con saldo.
                                </td>
                            </tr>
                        @endif
                    </tbody>


                </table>
                <div class="mt-4">
                    <p class="font-bold">Total de saldos: {{ $totalSaldos }}</p>
                </div>
            </div>
        </div>
</div>

</form>
<!-- Botones de Acción -->
<div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
    <span class="flex w-full rounded-md shadow-sm sm:ml-3 sm:w-auto">
        <button wire:click.prevent="guardar" type="button"
            class="inline-flex justify-center w-full rounded-md border border-transparent px-4 py-2 bg-purple-600 text-base leading-6 font-medium text-white shadow-sm hover:bg-purple-800 focus:outline-none focus:border-green-700 focus:shadow-outline-green transition ease-in-out duration-150 sm:text-sm sm:leading-5">Guardar</button>
    </span>
    <span class="flex w-full rounded-md shadow-sm sm:ml-3 sm:w-auto">
        <button wire:click="closeModal()" type="button"
            class="inline-flex justify-center w-full rounded-md border border-gray-300 px-4 py-2 bg-gray-200 text-base leading-6 font-medium text-gray-700 shadow-sm hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:shadow-outline-blue transition ease-in-out duration-150 sm:text-sm sm:leading-5">Cancelar</button>
    </span>
</div>
