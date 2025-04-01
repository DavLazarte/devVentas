<div class="py-12">
    <div class="max-w-9xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-3 py-4 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">PUNTO DE VENTA</h3>
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
            <div class="border-t border-gray-200">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-6">
                    <div class="col-span-1 md:col-span-2">
                        <!-- Artículos -->
                        <div class="relative mb-6">
                            <label for="articulo" class="block text-md-left font-medium text-gray-700">Buscar
                                Producto</label>
                            <input wire:model.debounce.300ms="searchArticulo" type="text"
                                class="block w-full mt-1 border border-gray-300 rounded-md py-2 px-3 text-black shadow-purple-200 focus:ring-purple-500 focus:border-purple-500"
                                placeholder="Escanear o ingresar código" autofocus>
                            @if ($searchArticulo)
                                <div class="absolute z-50 bg-white w-full rounded-md shadow-lg mt-1">
                                    @foreach ($articulo as $opcion)
                                        <div wire:click="agregarArticulo({{ $opcion['idarticulo'] }})"
                                            class="py-2 px-3 cursor-pointer hover:bg-gray-100">{{ $opcion['nombre'] }} |
                                            {{ $opcion['descripcion'] }}
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
                                        <th class="p-2">Producto</th>
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
                                                    <span
                                                        class="md:hidden block font-medium text-gray-700">Producto:</span>
                                                    {{ $art['nombre'] ?? '' }}
                                                </td>
                                                <td class="p-2 md:table-cell block md:table-cell">
                                                    <span
                                                        class="md:hidden block font-medium text-gray-700">Stock:</span>
                                                    {{ $art['stock'] ?? '' }}
                                                </td>
                                                <td class="p-2 md:table-cell block md:table-cell">
                                                    <span
                                                        class="md:hidden block font-medium text-gray-700">Precio:</span>
                                                    <input type="number" id="precio_unitario_{{ $index }}"
                                                        wire:model="articuloSeleccionado.{{ $index }}.precio_unitario"
                                                        wire:change="calcularSubTotalProducto({{ $index }})"
                                                        value="{{ isset($art['precio_unitario']) ? $art['precio_unitario'] : '' }}"
                                                        class="w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500">
                                                </td>
                                                <td class="p-2 md:table-cell block md:table-cell">
                                                    <span
                                                        class="md:hidden block font-medium text-gray-700">Cantidad:</span>
                                                    <input type="number" id="cantidad_{{ $index }}"
                                                        wire:model="articuloSeleccionado.{{ $index }}.cantidad"
                                                        wire:change="calcularSubTotalProducto({{ $index }})"
                                                        class="w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500">
                                                </td>
                                                <td class="p-3 md:table-cell block md:table-cell">
                                                    <span
                                                        class="md:hidden block font-medium text-gray-700">Subtotal:</span>
                                                    <span id="subtotal_{{ $index }}"
                                                        wire:model="articuloSeleccionado.{{ $index }}.subtotal"
                                                        wire:change="actualizarTotal({{ $index }})">
                                                        {{ isset($art['subtotal']) ? $art['subtotal'] : '' }}
                                                    </span>
                                                </td>
                                                <td class="p-2 md:table-cell block md:table-cell">
                                                    <span
                                                        class="md:hidden block font-medium text-gray-700">Acción:</span>
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
                        <h3 class="text-lg font-medium text-gray-900 mb-4">DETALLES DE LA VENTA</h3>
                        <!-- Tipo de Venta y Forma de Pago -->
                        <label for="tipo_venta" class="block text-sm font-medium text-gray-700">Tipo de
                            Venta:</label>
                        <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-2 gap-2">
                            <div class="relative h-full">
                                <input type="radio" name="tipo_venta" id="venta_rapida" value="venta_rapida"
                                    wire:model="tipo_venta" class="hidden peer">
                                <label for="venta_rapida"
                                    class="inline-flex items-center justify-center h-full w-full px-4 py-2 bg-white border-2 rounded-lg cursor-pointer border-neutral-200/70 text-neutral-600 peer-checked:border-purple-400 peer-checked:text-neutral-900 peer-checked:bg-purple-200/50 hover:text-neutral-900 hover:border-neutral-300">
                                    <div class="text-center text-sm opacity-60">Venta Rápida</div>
                                </label>
                            </div>
                            <div class="relative h-full">
                                <input type="radio" name="tipo_venta" id="cuenta_corriente" value="cuenta_corriente"
                                    wire:model="tipo_venta" class="hidden peer">
                                <label for="cuenta_corriente"
                                    class="inline-flex items-center justify-center h-full w-full px-4 py-2 bg-white border-2 rounded-lg cursor-pointer border-neutral-200/70 text-neutral-600 peer-checked:border-purple-400 peer-checked:text-neutral-900 peer-checked:bg-purple-200/50 hover:text-neutral-900 hover:border-neutral-300">
                                    <div class="text-center text-sm opacity-60">Cuenta Corriente</div>
                                </label>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-1 md:grid-cols-1 gap-2 mt-2">
                            <!-- Cliente -->
                            @if ($tipo_venta === 'cuenta_corriente')
                                <div class="relative mb-6">
                                    <label for="cliente"
                                        class="block text-sm font-medium text-gray-700">Cliente:</label>
                                    <input type="text" wire:model.debounce.300ms="searchCliente"
                                        class="block w-full mt-1 border border-gray-300 rounded-md py-2 px-3 text-black shadow-purple-200 focus:ring-purple-500 focus:border-purple-500"
                                        placeholder="Buscar un cliente">
                                    @if ($searchCliente)
                                        <div class="absolute z-50 bg-white w-full rounded-md shadow-lg mt-1">
                                            @foreach ($persona as $opcion)
                                                <div wire:click="agregarCliente({{ $opcion['idpersona'] }})"
                                                    class="py-2 px-3 cursor-pointer hover:bg-gray-100">
                                                    {{ $opcion['nombre'] }}</div>
                                            @endforeach
                                        </div>
                                    @endif
                                    @if ($clienteSeleccionado)
                                        <div class="mt-4">
                                            <label class="block text-sm font-medium text-gray-700">Cliente
                                                seleccionado</label>
                                            <input type="text" wire:model="nombre_cliente"
                                                class="block w-full bg-purple-500 text-white rounded-md py-2 px-3 shadow-sm sm:text-sm"
                                                placeholder="Cliente seleccionado">
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                        <label for="forma_de_pago" class="block text-sm font-medium text-gray-700 mt-2">Forma de
                            Pago:</label>
                        <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-2 gap-2">
                            <div class="relative h-full">
                                <input type="radio" name="forma_de_pago" id="efectivo" value="efectivo"
                                    wire:model="forma_de_pago" class="hidden peer">
                                <label for="efectivo"
                                    class="flex items-center justify-center h-full w-full px-4 py-2 bg-white border-2 rounded-lg cursor-pointer border-neutral-200/70 text-neutral-600 peer-checked:border-purple-400 peer-checked:text-neutral-900 peer-checked:bg-purple-200/50 hover:text-neutral-900 hover:border-neutral-300">
                                    <div class="text-sm opacity-60 text-center">Efectivo</div>
                                </label>
                            </div>
                            <div class="relative h-full">
                                <input type="radio" name="forma_de_pago" id="transferencia" value="transferencia"
                                    wire:model="forma_de_pago" class="hidden peer">
                                <label for="transferencia"
                                    class="flex items-center justify-center h-full w-full px-4 py-2 bg-white border-2 rounded-lg cursor-pointer border-neutral-200/70 text-neutral-600 peer-checked:border-purple-400 peer-checked:text-neutral-900 peer-checked:bg-purple-200/50 hover:text-neutral-900 hover:border-neutral-300">
                                    <div class="text-sm opacity-60 text-center">Transferencia</div>
                                </label>
                            </div>
                            <div class="relative h-full">
                                <input type="radio" name="forma_de_pago" id="cuenta_corriente_pago"
                                    value="cuenta_corriente" wire:model="forma_de_pago" class="hidden peer">
                                <label for="cuenta_corriente_pago"
                                    class="flex items-center justify-center h-full w-full px-4 py-2 bg-white border-2 rounded-lg cursor-pointer border-neutral-200/70 text-neutral-600 peer-checked:border-purple-400 peer-checked:text-neutral-900 peer-checked:bg-purple-200/50 hover:text-neutral-900 hover:border-neutral-300">
                                    <div class="text-sm opacity-60 text-center">A cuenta</div>
                                </label>
                            </div>
                            <div class="relative h-full">
                                <input type="radio" name="forma_de_pago" id="tarjeta" value="tarjeta"
                                    wire:model="forma_de_pago" class="hidden peer">
                                <label for="tarjeta"
                                    class="flex items-center justify-center h-full w-full px-4 py-2 bg-white border-2 rounded-lg cursor-pointer border-neutral-200/70 text-neutral-600 peer-checked:border-purple-400 peer-checked:text-neutral-900 peer-checked:bg-purple-200/50 hover:text-neutral-900 hover:border-neutral-300">
                                    <div class="text-sm opacity-60 text-center">Tarjeta</div>
                                </label>
                            </div>
                        </div>


                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-3 mt-2">
                            <div>
                                <label for="descuento"
                                    class="block text-sm font-medium text-gray-700">Descuento</label>
                                <input type="number" id="descuento" wire:model.debounce.500ms="descuento"
                                    class="block w-full mt-1 border-gray-300 rounded-md py-2 px-3 text-gray-700 shadow-purple-200 focus:ring-purple-500 focus:border-purple-500"
                                    placeholder="%">
                            </div>
                            <div>
                                <label for="racargo" class="block text-sm font-medium text-gray-700">Recargo</label>
                                <input type="number" id="recargo" wire:model.debounce.500ms="recargo"
                                    class="block w-full mt-1 border-gray-300 rounded-md py-2 px-3 text-gray-700 shadow-purple-200 focus:ring-purple-500 focus:border-purple-500"
                                    placeholder="%">
                            </div>
                        </div>
                        <div class="mb-4">
                            <label for="total" class="block text-lg font-semibold text-gray-700">Total</label>
                            <div
                                class="block w-full mt-1 text-3xl font-bold text-purple-600 bg-purple-100 border border-purple-300 rounded-md py-4 px-6 shadow-lg">
                                ${{ number_format($venta_total, 2) }}
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                            <div>
                                <label for="pago" class="block text-sm font-medium text-gray-700">Pago</label>
                                <input type="text" id="pago" wire:model.debounce.500ms="pago"
                                    class="block w-full mt-1 border-gray-300 rounded-md py-2 px-3 text-gray-700 shadow-purple-200
                                    focus:ring-purple-500 focus:border-purple-500"
                                    placeholder="$0.00">
                            </div>

                            <div>
                                <!-- Label dinámico según el saldo -->
                                <label for="cambio" class="block text-sm font-medium"
                                    :class="{ 'text-green-600': $wire.saldo < 0, 'text-red-600': $wire.saldo >= 0 }">
                                    <span x-text="$wire.saldo < 0 ? 'Cambio' : 'Saldo'"></span>
                                </label>

                                <!-- Muestra el saldo con wire:model -->
                                <span class="block w-full mt-1 px-3 py-2 text-lg font-semibold rounded-md text-center"
                                    :class="{
                                        'text-green-600 bg-green-100': $wire.saldo < 0,
                                        'text-red-600 bg-red-100': $wire
                                            .saldo >= 0
                                    }"
                                    x-text="'$' + Math.abs($wire.saldo).toFixed(2)" wire:model="saldo">
                                </span>
                            </div>
                        </div>
                        <button wire:click="guardar()"
                            class="w-full bg-purple-600 text-white py-3 px-6 rounded-lg shadow-lg transition-all duration-300 ease-in-out transform hover:scale-105 hover:bg-purple-700 focus:outline-none focus:ring-4 focus:ring-purple-400">
                            <span class="font-semibold text-lg tracking-wide">Cargar Venta</span>
                        </button>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@push('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        window.addEventListener('errorVenta', event => {
            Swal.fire({
                icon: 'error',
                title: 'Error en la Venta',
                text: event.detail.message,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Entendido'
            });
        });
    </script>
@endpush
