<div class="py-12">
    <div class="max-w-9xl mx-auto sm:px6 lg:px-8">
        <div class="mx-auto grid  grid-cols-12 gap-4 p-1">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg px-4 py-4 col-span-12 sm:col-span-7  md:col-span-9">

                <div class=" border-gray-900/10 pb-12">
                    <h1 class="text-base font-semibold leading-7 text-gray-900">INGRESO DE INSUMOS Y MERCADERIA</h1>
                    @if ($mensajeVenta)
                        <div class="bg-teal-100 rounded-b text-teal-900 px-4 py-4 shadow-md my-3" role="alert">
                            <div class="flex">
                                <div>
                                    <h4>{{ $mensajeVenta }}</h4>
                                </div>
                            </div>
                        </div>
                    @endif


                    <div class="mt-10 grid gap-x-6 gap-y-8">

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
                <div class="mt-3 ">
                    <div class="col-span-12">
                        <div class="overflow-auto lg:overflow-visible ">
                            <table class="table text-gray-400 border-separate space-y-6 text-sm w-full">
                                <thead class="bg-gray-800 text-gray-500">
                                    <tr>
                                        <th class="p-3">Producto</th>
                                        <th class="p-3 text-left">Stock</th>
                                        <th class="p-3 text-left">Precio</th>
                                        <th class="p-3 text-left">Cantidad</th>
                                        <th class="p-3 text-left">Subtotal</th>
                                        <th class="p-3 text-left">Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Iteración sobre la lista de materias prima seleccionada -->
                                    @foreach ($articuloSeleccionado as $index => $art)
                                        @if (!isset($art['eliminado']) || !$art['eliminado'])
                                            <tr class="bg-gray-800">
                                                <td class="p-3">
                                                    <input type="text" id="idarticulo_{{ $index }}"
                                                        wire:model="id_articulo"
                                                        value="{{ isset($art['idarticulo']) ? $art['idarticulo'] : '' }}"
                                                        placeholder="{{ isset($art['nombre']) ? $art['nombre'] : '' }}"
                                                        class="p-2 rounded border w-full">
                                                </td>

                                                <td class="p-3">
                                                    <input type="number" id="stock_{{ $index }}"
                                                        value="{{ isset($art['stock']) ? $art['stock'] : '' }}"
                                                        class="p-2 rounded border  w-3/4">
                                                </td>
                                                <td class="p-3">
                                                    <input type="number" id="precio_unitario_{{ $index }}" wire:model="articuloSeleccionado.{{ $index }}.precio_compra"
                                                        {{-- value="{{ isset($art['precio_unitario']) ? $art['precio_unitario'] : '' }}" --}}
                                                        class="p-2 rounded border w-3/4">
                                                </td>

                                                <td class="p-3">
                                                    <input type="number" id="cantidad_{{ $index }}"
                                                        wire:model="articuloSeleccionado.{{ $index }}.cantidad"
                                                        wire:change="calcularSubTotalProducto({{ $index }})"
                                                        class="p-2 rounded border w-1/2">
                                                </td>
                                                <td class="p-3">
                                                    <input type="number" id="subtotal_{{ $index }}"
                                                        wire:model="articuloSeleccionado.{{ $index }}.subtotal"
                                                        wire:change="actualizarTotal({{ $index }})"
                                                        class="p-2 rounded border w-full">
                                                </td>

                                                <td class="p-3"">
                                                    <button wire:click="eliminarArticulo({{ $index }})"
                                                        class="text-red-600 hover:text-red-800"><svg class="w-6 h-6 "
                                                            stroke="currentColor" fill="none"
                                                            xmlns="http://www.w3.org/2000/svg" x="0px" y="0px"
                                                            width="100" height="100" viewBox="0 0 24 24">
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
                </div>
            </div>
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg px-4 py-4 col-span-12 sm:col-span-5  md:col-span-3">
                <h1 class="text-base font-semibold leading-7 text-gray-900"> DETALLE COMPRA</h1>
                <div class="mt-4 mb-3">
                    <label for="num_recibo" class="block text-gray-700 text-sm font-bold mb-2">Numero de Recibo:</label>
                    <input type="number" id="num_recibo" name="num_recibo" wire:model="num_recibo"
                    class="block w-full border border-gray-300 rounded-md py-2 px-3 text-black"
                    placeholder="ingresar el numero de la factura o boleta">
                </div>
                <div class="mt-4">
                    <label for="tipo_pago" class="block text-gray-700 text-sm font-bold mb-2">Forma de Pago:</label>
                    <select id="tipo_pago" name="tipo_pago" wire:model="forma_de_pago"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <option value="efectivo" >Efectivo</option>
                        <option value="transferencia" >Transferencia</option>
                        <option value="cuenta_corriente">Cuenta</option>
                        <option value="tarjeta">Tarjeta</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label for="tipo_compra" class="block text-gray-700 text-sm font-bold mb-2">Tipo de Compra:</label>
                    <select id="tipo_compra" name="tipo_compra" wire:model="tipo_venta"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <option value="compra_rapida">Compra Rápida</option>
                        <option value="cuenta_corriente">Compra a Proveedor</option>
                    </select>
                </div>

                @if ($tipo_venta == 'cuenta_corriente')
                    <div class="mt-10  gap-x-6 gap-y-8 ">
                        <input wire:model.debounce.300ms="searchCliente" type="text"
                            class="block w-full border border-gray-300 rounded-md py-2 px-3 text-black"
                            placeholder="Buscar un proveedor">

                        @if ($searchCliente)
                            <div class="absolute z-50 bg-white w-full mt-1 rounded-md shadow-lg">
                                @foreach ($persona as $opcion)
                                    <div wire:click="agregarProveedor({{ $opcion['idpersona'] }})"
                                        class="py-2 px-3 cursor-pointer hover:bg-gray-100">
                                        {{ $opcion['nombre'] }}</div>
                                @endforeach
                            </div>
                        @endif
                        @if ($proveedorSeleccionado)
                            <div>
                                <label for="id_cliente"
                                    class="block text-sm font-medium leading-6 text-gray-900">Proveedor</label>
                                <div class="relative mt-2 rounded-md shadow-sm">
                                    <input type="text" id="idcliente" wire:model="nombre_cliente"
                                        class="absolute z-50 bg-white w-full mt-1 rounded-md shadow-lg">
                                </div>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="mt-4 mb-4">
                        <label for="id_cliente"
                            class="block text-sm font-medium leading-6 text-gray-900">Proveedor</label>
                        <div class="relative mt-2 rounded-md shadow-sm">
                            <input type="text" id="idcliente" wire:model="nombre_cliente"
                                class="block w-full rounded-md border-0 py-1.5 pl-7 pr-20 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-900 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                        </div>
                    </div>

                @endif


            </div>

        </div>
        <div class="mx-auto grid max-w-9xl grid-cols-12 gap-4 mt-5">
            <div class="bg-white shadow-sm rounded p-4 col-span-12 sm:col-span-4">
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
                    <input type="text" id="total" wire:model="compra_total" placeholder="$0,00"
                        class="p-2 rounded border attendees-count">
                </div>
            </div>

            <div class="bg-white shadow-sm rounded p-4 col-span-12 sm:col-span-4">
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
                    <input type="text" id="pago" wire:model="pago" placeholder="ingrese el monto abonado"
                        class="p-2 rounded border attendees-count" wire:change="calcularSaldo()">
                </div>
            </div>

            <div class="bg-white shadow-sm rounded p-4 col-span-12 sm:col-span-4">
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
                    <div class="text-sm text-gray-500">Saldo o cambio</div>
                    <input type="text" id="saldo" wire:model="saldo" placeholder="saldo"
                        class="p-2 rounded border attendees-count">
                </div>
            </div>
        </div>
        <button
            class="mt-4 py-2 px-4 rounded-lg bg-gray-900 text-white shadow-md hover:shadow-lg focus:opacity-85 active:opacity-85 mb-4 col-span-12 "
            type="button" wire:click.prevent="guardar()">
            GUARDAR COMPRA
        </button>
    </div>

</div>
