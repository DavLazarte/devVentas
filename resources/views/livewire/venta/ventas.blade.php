<div class="py-12">
    <div class="max-w-9xl mx-auto sm:px6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg px-4 py-4">

            <div class=" border-gray-900/10 pb-12">
                <h1 class="text-base font-semibold leading-7 text-gray-900">PUNTO DE VENTA</h1>
                @if ($mensajeVenta)
                    <div class="bg-teal-100 rounded-b text-teal-900 px-4 py-4 shadow-md my-3" role="alert">
                        <div class="flex">
                            <div>
                                <h4>{{ $mensajeVenta }}</h4>
                            </div>
                        </div>
                    </div>
                @endif


                <div class="mt-10 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                    <div class="sm:col-span-3">
                        <input wire:model.debounce.300ms="searchCliente" type="text"
                            class="block w-full border border-gray-300 rounded-md py-2 px-3 text-black"
                            placeholder="Buscar un cliente">

                        @if ($searchCliente)
                            <div class="absolute z-50 bg-white w-1/4 mt-1 rounded-md shadow-lg">
                                @foreach ($persona as $opcion)
                                    <div wire:click="agregarCliente({{ $opcion['idpersona'] }})"
                                        class="py-2 px-3 cursor-pointer hover:bg-gray-100">
                                        {{ $opcion['nombre'] }}</div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    <div class="sm:col-span-3">
                        <input wire:model.debounce.300ms="searchArticulo" type="text"
                            class="block w-full border border-gray-300 rounded-md py-2 px-3 text-black"
                            placeholder="escanear o ingresar codigo">

                        @if ($searchArticulo)
                            <div class="absolute z-50 bg-white w-1/4 mt-1 rounded-md shadow-lg">
                                @foreach ($articulo as $opcion)
                                    <div wire:click="agregarArticulo({{ $opcion['idarticulo'] }})"
                                        class="py-2 px-3 cursor-pointer hover:bg-gray-100">
                                        {{ $opcion['nombre'] }}</div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

            </div>
            @if ($clienteSeleccionado)
                <div>
                    <label for="id_cliente" class="block text-sm font-medium leading-6 text-gray-900">Cliente</label>
                    <div class="relative mt-2 rounded-md shadow-sm">
                        <input type="text" id="idcliente" wire:model="nombre_cliente"
                            class="block w-1/2 rounded-md border-0 py-1.5 pl-7 pr-20 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-900 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                    </div>
                </div>
            @endif


            <div class="overflow-x-auto mt-3">
                <table class="w-full border-collapse border border-gray-300">
                    <thead>
                        <tr class="bg-gray-200">
                            <th class="border-y border-gray-100 bg-gray-50/50 p-2">Producto</th>
                            <th class="border-y border-gray-100 bg-gray-50/50 p-2">Stock</th>
                            <th class="border-y border-gray-100 bg-gray-50/50 p-2">Precio</th>
                            <th class="border-y border-gray-100 bg-gray-50/50 ">Cantidad</th>
                            <th class="border-y border-gray-100 bg-gray-50/50 p-2">SubTotal</th>
                            <th class="border-y border-gray-100 bg-gray-50/50 p-2">Accion</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Iteración sobre la lista de materias prima seleccionada -->
                        @foreach ($articuloSeleccionado as $index => $art)
                            @if (!isset($art['eliminado']) || !$art['eliminado'])
                                <tr>
                                    <td>
                                        <input type="text" id="idarticulo_{{ $index }}"
                                            wire:model="id_articulo"
                                            value="{{ isset($art['idarticulo']) ? $art['idarticulo'] : '' }}"
                                            placeholder="{{ isset($art['nombre']) ? $art['nombre'] : '' }}"
                                            class="p-2 rounded border attendees-count">
                                    </td>

                                    <td class="border border-gray-200 px-4 py-2">
                                        <input type="number" id="stock_{{ $index }}"
                                            value="{{ isset($art['stock']) ? $art['stock'] : '' }}"
                                            class="p-2 rounded border attendees-count">
                                    </td>
                                    <td class="border border-gray-200 px-4 py-2">
                                        <input type="number" id="precio_unitario_{{ $index }}"
                                            value="{{ isset($art['precio_unitario']) ? $art['precio_unitario'] : '' }}"
                                            class="p-2 rounded border attendees-count">
                                    </td>

                                    <td class="border border-gray-200 px-4 py-2">
                                        <input type="number" id="cantidad_{{ $index }}"
                                            wire:model="articuloSeleccionado.{{ $index }}.cantidad"
                                            wire:change="calcularSubTotalProducto({{ $index }})"
                                            class="p-2 rounded border attendees-count">
                                    </td>

                                    <td class="border border-gray-200 px-4 py-2">
                                        <input type="number" id="subtotal_{{ $index }}"
                                            wire:model="articuloSeleccionado.{{ $index }}.subtotal"
                                            wire:change="actualizarTotal({{ $index }})">
                                    </td>

                                    <td class="border border-gray-200 px-4 py-2">
                                        <button wire:click="eliminarArticulo({{ $index }})"
                                            class="text-red-600 hover:text-red-800"><svg class="w-6 h-6 "
                                                stroke="currentColor" fill="none" xmlns="http://www.w3.org/2000/svg"
                                                x="0px" y="0px" width="100" height="100" viewBox="0 0 24 24">
                                                <path
                                                    d="M 10 2 L 9 3 L 4 3 L 4 5 L 5 5 L 5 20 C 5 20.522222 5.1913289 21.05461 5.5683594 21.431641 C 5.9453899 21.808671 6.4777778 22 7 22 L 17 22 C 17.522222 22 18.05461 21.808671 18.431641 21.431641 C 18.808671 21.05461 19 20.522222 19 20 L 19 5 L 20 5 L 20 3 L 15 3 L 14 2 L 10 2 z M 7 5 L 17 5 L 17 20 L 7 20 L 7 5 z M 9 7 L 9 18 L 11 18 L 11 7 L 9 7 z M 13 7 L 13 18 L 15 18 L 15 7 L 13 7 z">
                                                </path>
                                            </svg></button>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="container mx-auto mt-8 p-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white shadow-sm rounded p-4">
                    <div
                        class="flex items-center justify-center flex-shrink-0 h-12 w-12 rounded-xl bg-red-100 text-red-500">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                            </path>
                        </svg>
                    </div>
                    <div class="flex flex-col flex-grow ml-4">
                        <div class="text-sm text-gray-500">Total</div>
                        {{-- <div class="font-bold text-lg">$<span id="venta-total">0.00</span></div> --}}
                        <input type="text" id="total" wire:model="venta_total" placeholder="$0,00"
                            class="p-2 rounded border attendees-count">
                    </div>
                </div>

                <div class="bg-white shadow-sm rounded p-4">
                    <div
                        class="flex items-center justify-center flex-shrink-0 h-12 w-12 rounded-xl bg-red-100 text-red-500">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                            </path>
                        </svg>
                    </div>
                    <div class="flex flex-col flex-grow ml-4">
                        <div class="text-sm text-gray-500">Pago</div>
                        <input type="text" id="pago" wire:model="pago"
                            placeholder="ingrese el monto abonado" class="p-2 rounded border attendees-count"
                            wire:change="calcularSaldo()">
                    </div>
                </div>

                <div class="bg-white shadow-sm rounded p-4">
                    <div
                        class="flex items-center justify-center flex-shrink-0 h-12 w-12 rounded-xl bg-red-100 text-red-500">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                            </path>
                        </svg>
                    </div>
                    <div class="flex flex-col flex-grow ml-4">
                        <div class="text-sm text-gray-500">Saldo</div>
                        <input type="text" id="saldo" wire:model="saldo" placeholder="saldo"
                            class="p-2 rounded border attendees-count">
                    </div>
                </div>
            </div>
            <button
                class="w-3/4 mt-4 py-2 px-4 rounded-lg bg-gray-900 text-white shadow-md hover:shadow-lg focus:opacity-85 active:opacity-85 mb-4"
                type="button" wire:click.prevent="guardar()">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"
                    stroke-width="2" class="h-4 w-4">
                    <path
                        d="M6.25 6.375a4.125 4.125 0 118.25 0 4.125 4.125 0 01-8.25 0zM3.25 19.125a7.125 7.125 0 0114.25 0v.003l-.001.119a.75.75 0 01-.363.63 13.067 13.067 0 01-6.761 1.873c-2.472 0-4.786-.684-6.76-1.873a.75.75 0 01-.364-.63l-.001-.122zM19.75 7.5a.75.75 0 00-1.5 0v2.25H16a.75.75 0 000 1.5h2.25v2.25a.75.75 0 001.5 0v-2.25H22a.75.75 0 000-1.5h-2.25V7.5z">
                    </path>
                </svg>
                GUARDAR VENTA
            </button>
        </div>


    </div>

</div>
