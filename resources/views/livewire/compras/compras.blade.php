<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">INGRESO DE MERCADERIA</h3>
                @if ($mensajeVenta)
                    <div class="bg-teal-100 rounded-b text-teal-900 px-4 py-4 shadow-md my-3" role="alert">
                        <div class="flex">
                            <div>
                                <h4>{{ $mensajeVenta }}</h4>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Detalles de la Compra/Compra -->
            <div class="border-t border-gray-200 px-6 py-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="col-span-1 md:col-span-3 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <label for="num_recibo" class="block text-sm font-medium text-gray-700">Numero de Recibo:</label>
                        <input type="number" id="num_recibo" name="num_recibo" wire:model="num_recibo"
                            class="mt-1 block w-full rounded-md border-gray-300 py-2 px-3 text-gray-900 shadow-sm focus:ring-purple-500 focus:border-purple-500 sm:text-sm"
                            placeholder="Ingresar el numero de la factura o boleta">
                    </div>
                    <div>
                        <label for="tipo_venta" class="block text-sm font-medium text-gray-700">Tipo de Compra:</label>
                        <select id="tipo_venta" name="tipo_venta" wire:model="tipo_venta"
                            class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-purple-500 focus:border-purple-500 sm:text-sm rounded-md">
                            <option value="venta_rapida">Compra Rápida</option>
                            <option value="cuenta_corriente">Cuenta Corriente</option>
                        </select>
                    </div>
                    <div>
                        <label for="forma_de_pago" class="block text-sm font-medium text-gray-700">Forma de Pago:</label>
                        <select id="forma_de_pago" wire:model="forma_de_pago"
                            class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-purple-500 focus:border-purple-500 sm:text-sm rounded-md">
                            <option value="efectivo">Efectivo</option>
                            <option value="transferencia">Transferencia</option>
                            <option value="cuenta_corriente">Cuenta</option>
                            <option value="tarjeta">Tarjeta</option>
                        </select>
                    </div>

                    @if ($tipo_venta === 'cuenta_corriente')
                        <div>
                            <label for="cliente" class="block text-sm font-medium text-gray-700">Proveedor:</label>
                            <input type="text" wire:model.debounce.300ms="searchCliente"
                                class="block w-full mt-1 border border-gray-300 rounded-md py-2 px-3 text-black shadow-purple-200 focus:ring-purple-500 focus:border-purple-500"
                                placeholder="Buscar un cliente">
                            @if ($searchCliente)
                                <div class="absolute z-50 bg-white  rounded-md shadow-lg mt-1">
                                    @foreach ($persona as $opcion)
                                        <div wire:click="agregarProveedor({{ $opcion['idpersona'] }})"
                                            class="py-2 px-3 cursor-pointer hover:bg-gray-100">
                                            {{ $opcion['nombre'] }}</div>
                                    @endforeach
                                </div>
                            @endif
                            @if ($proveedorSeleccionado)
                                <div class="mt-4">
                                    <input type="text" wire:model="nombre_cliente"
                                        class="block w-full rounded-md border-gray-300 py-2 px-3 text-gray-900 shadow-sm focus:ring-purple-500 focus:border-purple-500 sm:text-sm"
                                        placeholder="Cliente seleccionado">
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <!-- Artículos y Detalle de Pago -->
            <div class="border-t border-gray-200 px-6 py-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="col-span-1 md:col-span-2">
                    <!-- Artículos -->
                    <div class="relative mb-6">
                        <label for="articulo" class="block text-sm font-medium text-gray-700">Artículos:</label>
                        <input wire:model.debounce.300ms="searchArticulo" type="text" 
                            class="block w-full mt-1 border border-gray-300 rounded-md py-2 px-3 text-black shadow-purple-200 focus:ring-purple-500 focus:border-purple-500"
                            placeholder="Escanear o ingresar código">
                        @if ($searchArticulo)
                            <div class="absolute z-50 bg-white w-full rounded-md shadow-lg mt-1">
                                @foreach ($articulo as $opcion)
                                    <div wire:click="agregarArticulo({{ $opcion['idarticulo'] }})"
                                        class="py-2 px-3 cursor-pointer hover:bg-gray-100">
                                        {{ $opcion['nombre'] }} | {{ $opcion['descripcion'] }}
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <!-- Tabla de Artículos -->
                    <div class="overflow-auto rounded-md shadow">
                        <table class="table-auto w-full text-gray-600">
                            <thead class="bg-gray-100 text-gray-800">
                                <tr class="hidden md:table-row">
                                    <th class="p- text-left">Producto</th>
                                    <th class="p-2 text-left">Stock</th>
                                    <th class="p-2 text-left">Precio</th>
                                    <th class="p-2 text-left">Cantidad</th>
                                    <th class="p-2 text-left">Subtotal</th>
                                    <th class="p-2 text-left">Acción</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white">
                                @foreach ($articuloSeleccionado as $index => $art)
                                    @if (!isset($art['eliminado']) || !$art['eliminado'])
                                        <tr class="md:table-row block md:table-row">
                                            <td class="p-2 md:table-cell block md:table-cell">
                                                <span class="md:hidden block font-medium text-gray-700">Producto:</span>
                                                {{ $art['nombre'] ?? '' }}</td>
                                            <td class="p-2 md:table-cell block md:table-cell">
                                                <span class="md:hidden block font-medium text-gray-700">Stock:</span>
                                                {{ $art['stock'] ?? '' }}</td>
                                            <td class="p- md:table-cell block md:table-cell">
                                                <span class="md:hidden block font-medium text-gray-700">Precio:</span>
                                                <input type="number" id="precio_compra_{{ $index }}"
                                                    wire:model="articuloSeleccionado.{{ $index }}.precio_compra"
                                                    wire:change="calcularSubTotalProducto({{ $index }})"
                                                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500">
                                            </td>
                                            <td class="p-2 md:table-cell block md:table-cell">
                                                <span class="md:hidden block font-medium text-gray-700">Cantidad:</span>
                                                <input type="number" id="cantidad_{{ $index }}"
                                                    wire:model="articuloSeleccionado.{{ $index }}.cantidad"
                                                    wire:change="calcularSubTotalProducto({{ $index }})"
                                                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500">
                                            </td>
                                            <td class="p-2 md:table-cell block md:table-cell">
                                                <span class="md:hidden block font-medium text-gray-700">Subtotal:</span>
                                                <span id="subtotal_{{ $index }}" class="block">
                                                    {{ isset($art['subtotal']) ? $art['subtotal'] : '' }}
                                                </span>
                                            </td>
                                            <td class="p-2 md:table-cell block md:table-cell">
                                                <span class="md:hidden block font-medium text-gray-700">Acción:</span>
                                                <button wire:click="eliminarArticulo({{ $index }})"
                                                    class="text-red-500 hover:text-red-700">
                                                    <svg class="w-6 h-6" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M6 18L18 6M6 6l12 12" stroke-linecap="round"
                                                            stroke-linejoin="round" stroke-width="2" />
                                                    </svg>
                                                </button>
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Detalle de Pago -->
                <div class="col-span-1 bg-white p-6 shadow rounded-lg">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">DETALLE COMPRA</h3>
                    <div class="mb-4">
                        <label for="total" class="block text-sm font-medium text-gray-700">Total</label>
                        <input type="text" id="total" wire:model="compra_total"
                            class="block w-full mt-1 border-gray-300 rounded-md py-2 px-3 text-gray-700 shadow-purple-200 focus:ring-purple-500 focus:border-purple-500"
                            placeholder="$0.00">
                    </div>
                    <div class="mb-4">
                        <label for="pago" class="block text-sm font-medium text-gray-700">Pago</label>
                        <input type="text" id="pago" wire:model="pago" wire:change="calcularSaldo()"
                            class="block w-full mt-1 border-gray-300 rounded-md py-2 px-3 text-gray-700 shadow-purple-200 focus:ring-purple-500 focus:border-purple-500"
                            placeholder="$0.00">
                    </div>
                    <div class="mb-4">
                        <label for="cambio" class="block text-sm font-medium text-gray-700">Cambio o Saldo</label>
                        <input type="text" id="cambio" wire:model="saldo"
                            class="block w-full mt-1 border-gray-300 rounded-md py-2 px-3 text-gray-700 shadow-purple-200 focus:ring-purple-500 focus:border-purple-500"
                            placeholder="$0.00">
                    </div>
                    <button wire:click="guardar()"
                        class="w-full bg-purple-600 text-white py-2 px-4 rounded-md shadow-sm hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                        Cargar Compra
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
