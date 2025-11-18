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
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 sm:gap-4 p-3 sm:p-6">
                    <div class="col-span-1 md:col-span-2">
                        <!-- Artículos -->
                        <div class="relative mb-4 sm:mb-6">
                            <div class="flex items-center justify-between mb-1.5 sm:mb-2">
                                <label for="articulo"
                                    class="block text-sm sm:text-base font-medium text-gray-700">Buscar Producto</label>
                                <button type="button" wire:click="$emit('abrirModalCrearArticulo')"
                                    class="inline-flex items-center gap-1 text-xs sm:text-sm font-medium text-purple-600 hover:text-purple-800">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 4v16m8-8H4" />
                                    </svg>
                                    Nuevo producto
                                </button>
                            </div>
                            <input wire:model.debounce.300ms="searchArticulo" type="text"
                                class="block w-full text-sm sm:text-base border border-gray-300 rounded-md py-2 px-3 text-black shadow-purple-200 focus:ring-purple-500 focus:border-purple-500"
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
                        <!-- Selector de variantes -->
                        @if ($mostrar_variantes && count($variantes_disponibles) > 0)
                            <div class="bg-gray-100 border rounded-md shadow-md p-3 sm:p-4 mb-3 sm:mb-4">
                                <h4 class="text-sm sm:text-base font-semibold text-gray-800 mb-2 sm:mb-3">Selecciona una variante</h4>
                                <ul class="space-y-2">
                                    @foreach ($variantes_disponibles as $variante)
                                        <li>
                                            <button wire:click="seleccionarVariante({{ $variante->id_variante }})"
                                                class="w-full flex justify-between items-center px-3 sm:px-4 py-2 bg-white hover:bg-purple-100 border rounded-md shadow-sm transition text-xs sm:text-sm">
                                                <span class="flex-1 text-left pr-2">{{ $variante->descripcion_variante }} (Stock:
                                                    {{ $variante->stock }})</span>
                                                <span
                                                    class="font-bold text-purple-600 whitespace-nowrap">${{ number_format($variante->precio_unitario, 2) }}</span>
                                            </button>
                                        </li>
                                    @endforeach
                                </ul>
                                <button wire:click="cerrarSelectorVariantes"
                                    class="mt-2 sm:mt-3 text-xs sm:text-sm text-red-500 hover:underline">Cancelar</button>
                            </div>
                        @endif


                        <!-- Tabla de Artículos -->
                        <div class="overflow-auto rounded-md shadow">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50 hidden md:table-header-group">
                                    <tr>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Producto</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Stock</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Precio</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Cantidad</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Subtotal</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Acción</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($articuloSeleccionado as $index => $art)
                                        @if (!isset($art['eliminado']) || !$art['eliminado'])
                                            <!-- Mobile: Card Design / Desktop: Table Row -->
                                            <tr
                                                class="hover:bg-gray-50 md:table-row border-b md:border-none bg-white md:bg-transparent">
                                                <!-- Mobile Card Container -->
                                                <td colspan="6" class="md:contents">
                                                    <!-- Mobile Card - Compacto Horizontal -->
                                                    <div class="md:hidden p-2.5 border-b border-gray-200 last:border-b-0 bg-white hover:bg-gray-50 transition-colors overflow-hidden">
                                                        <div class="flex items-center gap-2 min-w-0">
                                                            <!-- Nombre del Producto -->
                                                            <div class="flex-1 min-w-0 overflow-hidden">
                                                                <p class="text-sm font-semibold text-gray-900 leading-tight line-clamp-2 break-words">{{ $art['nombre'] ?? '' }}</p>
                                                                <p class="text-xs text-gray-500 mt-0.5">Stock: {{ $art['stock'] ?? '' }}</p>
                                                            </div>

                                                            <!-- Precio Input Compacto -->
                                                            <div class="w-20 flex-shrink-0">
                                                                <div class="relative">
                                                                    <div class="absolute inset-y-0 left-0 pl-1.5 flex items-center pointer-events-none">
                                                                        <span class="text-gray-500 text-xs">$</span>
                                                                    </div>
                                                                    <input type="text" id="precio_unitario_{{ $index }}"
                                                                        wire:model="articuloSeleccionado.{{ $index }}.precio_unitario"
                                                                        wire:change="calcularSubTotalProducto({{ $index }})"
                                                                        class="block w-full pl-5 pr-1.5 py-1.5 text-xs border-gray-300 rounded focus:ring-purple-500 focus:border-purple-500 text-right" />
                                                                </div>
                                                            </div>

                                                            <!-- Cantidad Selector Compacto -->
                                                            <div class="flex items-center flex-shrink-0">
                                                                <button type="button"
                                                                    wire:click="decrementarCantidad({{ $index }})"
                                                                    class="bg-gray-200 w-7 h-7 rounded-full flex items-center justify-center hover:bg-gray-300 transition">
                                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                                        viewBox="0 0 24 24">
                                                                        <path d="M20 12H4" stroke-width="2.5"
                                                                            stroke-linecap="round" stroke-linejoin="round" />
                                                                    </svg>
                                                                </button>
                                                                <input type="text" id="cantidad_{{ $index }}"
                                                                    wire:model="articuloSeleccionado.{{ $index }}.cantidad"
                                                                    wire:change="calcularSubTotalProducto({{ $index }})"
                                                                    class="w-8 text-center text-sm font-medium border-0 focus:ring-0 focus:outline-none bg-transparent"
                                                                    min="1">
                                                                <button type="button"
                                                                    wire:click="incrementarCantidad({{ $index }})"
                                                                    class="bg-gray-200 w-7 h-7 rounded-full flex items-center justify-center hover:bg-gray-300 transition">
                                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                                        viewBox="0 0 24 24">
                                                                        <path d="M12 4v16m8-8H4" stroke-width="2.5"
                                                                            stroke-linecap="round" stroke-linejoin="round" />
                                                                    </svg>
                                                                </button>
                                                            </div>

                                                            <!-- Subtotal -->
                                                            <div class="w-20 flex-shrink-0 text-right">
                                                                <p class="text-xs text-gray-500 mb-0.5">Subtotal</p>
                                                                <p class="text-sm font-semibold text-gray-900">
                                                                    ${{ isset($art['subtotal']) ? number_format($art['subtotal'], 2) : '0.00' }}
                                                                </p>
                                                            </div>

                                                            <!-- Botón Eliminar -->
                                                            <button wire:click="eliminarArticulo({{ $index }})"
                                                                class="flex-shrink-0 inline-flex items-center justify-center p-1.5 rounded-md text-red-600 hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500 transition">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                                    viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                                    <path d="M6 18L18 6M6 6l12 12" stroke-linecap="round"
                                                                        stroke-linejoin="round" stroke-width="2" />
                                                                </svg>
                                                            </button>
                                                        </div>
                                                    </div>

                                                    <!-- Desktop Table Cells -->
                                                    <!-- Producto -->
                                                    <td class="hidden md:table-cell py-2 px-6">
                                                        <span class="text-gray-900 text-sm">{{ $art['nombre'] ?? '' }}</span>
                                                    </td>

                                                    <!-- Stock -->
                                                    <td class="hidden md:table-cell py-2 px-6">
                                                        <span class="text-gray-900 text-sm">{{ $art['stock'] ?? '' }}</span>
                                                    </td>

                                                    <!-- Precio -->
                                                    <td class="hidden md:table-cell py-2 px-4">
                                                        <div class="relative">
                                                            <div
                                                                class="absolute inset-y-0 left-0 pl-2 flex items-center pointer-events-none">
                                                                <span class="text-gray-500 text-sm">$</span>
                                                            </div>
                                                            <input type="text" id="precio_unitario_desktop_{{ $index }}"
                                                                wire:model="articuloSeleccionado.{{ $index }}.precio_unitario"
                                                                wire:change="calcularSubTotalProducto({{ $index }})"
                                                                class="pl-6 pr-2 py-1 w-full focus:ring-purple-500 focus:border-purple-500 block text-sm border-gray-300 rounded-md text-right" />
                                                        </div>
                                                    </td>

                                                    <!-- Cantidad -->
                                                    <td class="hidden md:table-cell py-2 px-4">
                                                        <div class="flex items-center w-28 lg:w-32">
                                                            <button type="button"
                                                                wire:click="decrementarCantidad({{ $index }})"
                                                                class="bg-gray-200 px-2 py-1 rounded-l-md hover:bg-gray-300 transition">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                                    viewBox="0 0 24 24">
                                                                    <path d="M20 12H4" stroke-width="2"
                                                                        stroke-linecap="round" stroke-linejoin="round" />
                                                                </svg>
                                                            </button>
                                                            <input type="text" id="cantidad_desktop_{{ $index }}"
                                                                wire:model="articuloSeleccionado.{{ $index }}.cantidad"
                                                                wire:change="calcularSubTotalProducto({{ $index }})"
                                                                class="text-center w-full px-2 py-1 border-gray-300 focus:ring-purple-500 focus:border-purple-500 text-sm"
                                                                min="1">
                                                            <button type="button"
                                                                wire:click="incrementarCantidad({{ $index }})"
                                                                class="bg-gray-200 px-2 py-1 rounded-r-md hover:bg-gray-300 transition">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                                    viewBox="0 0 24 24">
                                                                    <path d="M12 4v16m8-8H4" stroke-width="2"
                                                                        stroke-linecap="round" stroke-linejoin="round" />
                                                                </svg>
                                                            </button>
                                                        </div>
                                                    </td>

                                                    <!-- Subtotal -->
                                                    <td class="hidden md:table-cell py-2 px-6">
                                                        <span class="font-medium text-gray-900 text-sm">
                                                            ${{ isset($art['subtotal']) ? number_format($art['subtotal'], 2) : '0.00' }}
                                                        </span>
                                                    </td>

                                                    <!-- Acción -->
                                                    <td class="hidden md:table-cell py-2 px-6">
                                                        <div class="flex justify-center">
                                                            <button wire:click="eliminarArticulo({{ $index }})"
                                                                class="inline-flex items-center justify-center p-1 border border-transparent rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                                    viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                                    <path d="M6 18L18 6M6 6l12 12" stroke-linecap="round"
                                                                        stroke-linejoin="round" stroke-width="2" />
                                                                </svg>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!-- Detalle de Pago -->
                    <div class="col-span-1 bg-white p-3 sm:p-6 shadow rounded-lg">
                        <h3 class="text-base sm:text-lg font-medium text-gray-900 mb-3 sm:mb-4">DETALLES DE LA VENTA</h3>
                        <!-- Tipo de Venta y Forma de Pago -->
                        <label for="tipo_venta" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1.5 sm:mb-2">Tipo de
                            Venta:</label>
                        <div class="grid grid-cols-2 gap-2 mb-3 sm:mb-4">
                            <div class="relative h-full">
                                <input type="radio" name="tipo_venta" id="venta_rapida" value="venta_rapida"
                                    wire:model="tipo_venta" class="hidden peer">
                                <label for="venta_rapida"
                                    class="inline-flex items-center justify-center h-full w-full px-2 sm:px-4 py-1.5 sm:py-2 bg-white border-2 rounded-lg cursor-pointer border-neutral-200/70 text-neutral-600 peer-checked:border-purple-400 peer-checked:text-neutral-900 peer-checked:bg-purple-200/50 hover:text-neutral-900 hover:border-neutral-300">
                                    <div class="text-center text-xs sm:text-sm opacity-60">Venta Rápida</div>
                                </label>
                            </div>
                            <div class="relative h-full">
                                <input type="radio" name="tipo_venta" id="cuenta_corriente"
                                    value="cuenta_corriente" wire:model="tipo_venta" class="hidden peer">
                                <label for="cuenta_corriente"
                                    class="inline-flex items-center justify-center h-full w-full px-2 sm:px-4 py-1.5 sm:py-2 bg-white border-2 rounded-lg cursor-pointer border-neutral-200/70 text-neutral-600 peer-checked:border-purple-400 peer-checked:text-neutral-900 peer-checked:bg-purple-200/50 hover:text-neutral-900 hover:border-neutral-300">
                                    <div class="text-center text-xs sm:text-sm opacity-60">Cuenta Corriente</div>
                                </label>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 gap-2 mt-2 sm:mt-3">
                            <!-- Cliente -->
                            @if ($tipo_venta === 'cuenta_corriente')
                                <div class="relative mb-4 sm:mb-6">
                                    <label for="cliente"
                                        class="block text-xs sm:text-sm font-medium text-gray-700 mb-1.5 sm:mb-2">Cliente:</label>
                                    <input type="text" wire:model.debounce.300ms="searchCliente"
                                        class="block w-full text-sm sm:text-base border border-gray-300 rounded-md py-2 px-3 text-black shadow-purple-200 focus:ring-purple-500 focus:border-purple-500"
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
                                        <div class="mt-3 sm:mt-4">
                                            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1.5 sm:mb-2">Cliente
                                                seleccionado</label>
                                            <input type="text" wire:model="nombre_cliente"
                                                class="block w-full text-xs sm:text-sm bg-purple-500 text-white rounded-md py-2 px-3 shadow-sm"
                                                placeholder="Cliente seleccionado">
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                        <label for="forma_de_pago" class="block text-xs sm:text-sm font-medium text-gray-700 mt-2 sm:mt-3 mb-1.5 sm:mb-2">Forma de
                            Pago:</label>
                        <div class="grid grid-cols-2 gap-2 mb-3 sm:mb-4">
                            <div class="relative h-full">
                                <input type="radio" name="forma_de_pago" id="efectivo" value="efectivo"
                                    wire:model="forma_de_pago" class="hidden peer">
                                <label for="efectivo"
                                    class="flex items-center justify-center h-full w-full px-2 sm:px-4 py-1.5 sm:py-2 bg-white border-2 rounded-lg cursor-pointer border-neutral-200/70 text-neutral-600 peer-checked:border-purple-400 peer-checked:text-neutral-900 peer-checked:bg-purple-200/50 hover:text-neutral-900 hover:border-neutral-300">
                                    <div class="text-xs sm:text-sm opacity-60 text-center">Efectivo</div>
                                </label>
                            </div>
                            <div class="relative h-full">
                                <input type="radio" name="forma_de_pago" id="transferencia" value="transferencia"
                                    wire:model="forma_de_pago" class="hidden peer">
                                <label for="transferencia"
                                    class="flex items-center justify-center h-full w-full px-2 sm:px-4 py-1.5 sm:py-2 bg-white border-2 rounded-lg cursor-pointer border-neutral-200/70 text-neutral-600 peer-checked:border-purple-400 peer-checked:text-neutral-900 peer-checked:bg-purple-200/50 hover:text-neutral-900 hover:border-neutral-300">
                                    <div class="text-xs sm:text-sm opacity-60 text-center">Transferencia</div>
                                </label>
                            </div>
                            <div class="relative h-full">
                                <input type="radio" name="forma_de_pago" id="cuenta_corriente_pago"
                                    value="cuenta_corriente" wire:model="forma_de_pago" class="hidden peer">
                                <label for="cuenta_corriente_pago"
                                    class="flex items-center justify-center h-full w-full px-2 sm:px-4 py-1.5 sm:py-2 bg-white border-2 rounded-lg cursor-pointer border-neutral-200/70 text-neutral-600 peer-checked:border-purple-400 peer-checked:text-neutral-900 peer-checked:bg-purple-200/50 hover:text-neutral-900 hover:border-neutral-300">
                                    <div class="text-xs sm:text-sm opacity-60 text-center">A cuenta</div>
                                </label>
                            </div>
                            <div class="relative h-full">
                                <input type="radio" name="forma_de_pago" id="tarjeta" value="tarjeta"
                                    wire:model="forma_de_pago" class="hidden peer">
                                <label for="tarjeta"
                                    class="flex items-center justify-center h-full w-full px-2 sm:px-4 py-1.5 sm:py-2 bg-white border-2 rounded-lg cursor-pointer border-neutral-200/70 text-neutral-600 peer-checked:border-purple-400 peer-checked:text-neutral-900 peer-checked:bg-purple-200/50 hover:text-neutral-900 hover:border-neutral-300">
                                    <div class="text-xs sm:text-sm opacity-60 text-center">Tarjeta</div>
                                </label>
                            </div>
                        </div>


                        <div class="grid grid-cols-2 gap-2 sm:gap-4 mb-3 sm:mb-4 mt-2 sm:mt-3">
                            <div>
                                <label for="descuento"
                                    class="block text-xs sm:text-sm font-medium text-gray-700 mb-1.5 sm:mb-2">Descuento</label>
                                <input type="number" id="descuento" wire:model.debounce.500ms="descuento"
                                    class="block w-full text-sm sm:text-base border-gray-300 rounded-md py-2 px-2 sm:px-3 text-gray-700 shadow-purple-200 focus:ring-purple-500 focus:border-purple-500"
                                    placeholder="%">
                            </div>
                            <div>
                                <label for="racargo" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1.5 sm:mb-2">Recargo</label>
                                <input type="number" id="recargo" wire:model.debounce.500ms="recargo"
                                    class="block w-full text-sm sm:text-base border-gray-300 rounded-md py-2 px-2 sm:px-3 text-gray-700 shadow-purple-200 focus:ring-purple-500 focus:border-purple-500"
                                    placeholder="%">
                            </div>
                        </div>
                        <div class="mb-3 sm:mb-4">
                            <label for="total" class="block text-base sm:text-lg font-semibold text-gray-700 mb-1.5 sm:mb-2">Total</label>
                            <div
                                class="block w-full text-2xl sm:text-3xl font-bold text-purple-600 bg-purple-100 border border-purple-300 rounded-md py-3 sm:py-4 px-4 sm:px-6 shadow-lg">
                                ${{ number_format($venta_total, 2) }}
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4 mb-4 sm:mb-6">
                            <div>
                                <label for="pago" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1.5 sm:mb-2">Pago</label>
                                <input type="number" id="pago" wire:model.debounce.500ms="pago"
                                    class="block w-full text-sm sm:text-base border-gray-300 rounded-md py-2 px-2 sm:px-3 text-gray-700 shadow-purple-200
                                    focus:ring-purple-500 focus:border-purple-500"
                                    placeholder="$0.00">
                            </div>

                            <div>
                                <!-- Label dinámico según el saldo -->
                                <label for="cambio" class="block text-xs sm:text-sm font-medium mb-1.5 sm:mb-2"
                                    :class="{ 'text-green-600': $wire.saldo < 0, 'text-red-600': $wire.saldo >= 0 }">
                                    <span x-text="$wire.saldo < 0 ? 'Cambio' : 'Saldo'"></span>
                                </label>

                                <!-- Muestra el saldo con wire:model -->
                                <span class="block w-full px-2 sm:px-3 py-2 text-base sm:text-lg font-semibold rounded-md text-center"
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
                            class="w-full bg-purple-600 text-white py-2.5 sm:py-3 px-4 sm:px-6 rounded-lg shadow-lg transition-all duration-300 ease-in-out transform hover:scale-105 hover:bg-purple-700 focus:outline-none focus:ring-4 focus:ring-purple-400">
                            <span class="font-semibold text-base sm:text-lg tracking-wide">Cargar Venta</span>
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
                buttonsStyling: false,
                confirmButtonText: 'Entendido',
                customClass: {
                    confirmButton: 'bg-indigo-800 text-white px-4 py-2 rounded mr-2',
                }
            });
        });
    </script>
@endpush

@livewire('articulo.articulo-livewire', ['layout' => 'ventas'])
