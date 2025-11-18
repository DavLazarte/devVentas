<div class="py-12">
    <div class="max-w-9xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-3 py-4 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">INGRESO DE MERCADERÍA</h3>
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
                        <!-- Información de Factura -->
                        <div class="bg-gray-50 p-3 sm:p-4 rounded-lg mb-4 sm:mb-6">
                            <h3 class="text-base sm:text-lg font-medium text-gray-900 mb-3 sm:mb-4">Información de
                                Factura</h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 sm:gap-4">
                                <div>
                                    <label for="num_recibo"
                                        class="block text-xs sm:text-sm font-medium text-gray-700 mb-1.5 sm:mb-2">Número
                                        de Recibo/Factura *</label>
                                    <input type="text" id="num_recibo" name="num_recibo" wire:model="num_recibo"
                                        class="block w-full text-sm sm:text-base rounded-md border-gray-300 py-2 px-3 text-gray-900 shadow-sm focus:ring-purple-500 focus:border-purple-500"
                                        placeholder="Opcional: Ej: 001-00012345">
                                    <p class="mt-1 text-xs text-gray-500">Dejar vacío si no tiene factura</p>
                                </div>
                                <div>
                                    <label for="tipo_venta"
                                        class="block text-xs sm:text-sm font-medium text-gray-700 mb-1.5 sm:mb-2">Tipo
                                        de Compra:</label>
                                    <select id="tipo_venta" name="tipo_venta" wire:model="tipo_venta"
                                        class="block w-full text-sm sm:text-base pl-3 pr-10 py-2 border-gray-300 focus:outline-none focus:ring-purple-500 focus:border-purple-500 rounded-md">
                                        <option value="venta_rapida">Compra Rápida</option>
                                        <option value="cuenta_corriente">Cuenta Corriente</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="forma_de_pago"
                                        class="block text-xs sm:text-sm font-medium text-gray-700 mb-1.5 sm:mb-2">Forma
                                        de Pago:</label>
                                    <select id="forma_de_pago" wire:model="forma_de_pago"
                                        class="block w-full text-sm sm:text-base pl-3 pr-10 py-2 border-gray-300 focus:outline-none focus:ring-purple-500 focus:border-purple-500 rounded-md">
                                        <option value="efectivo">Efectivo</option>
                                        <option value="transferencia">Transferencia</option>
                                        <option value="cuenta_corriente">Cuenta</option>
                                        <option value="tarjeta">Tarjeta</option>
                                    </select>
                                </div>
                            </div>
                            @if ($tipo_venta === 'cuenta_corriente')
                                <div class="mt-3 sm:mt-4">
                                    <label for="cliente"
                                        class="block text-xs sm:text-sm font-medium text-gray-700 mb-1.5 sm:mb-2">Proveedor:</label>
                                    <div class="relative">
                                        <input type="text" wire:model.debounce.300ms="searchCliente"
                                            class="block w-full text-sm sm:text-base border border-gray-300 rounded-md py-2 px-3 text-black shadow-purple-200 focus:ring-purple-500 focus:border-purple-500"
                                            placeholder="Buscar proveedor">
                                        @if ($searchCliente)
                                            <div class="absolute z-50 bg-white w-full rounded-md shadow-lg mt-1">
                                                @foreach ($persona as $opcion)
                                                    <div wire:click="agregarProveedor({{ $opcion['idpersona'] }})"
                                                        class="py-2 px-3 cursor-pointer hover:bg-gray-100 text-sm">
                                                        {{ $opcion['nombre'] }}
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                    @if ($proveedorSeleccionado)
                                        <div class="mt-3">
                                            <input type="text" wire:model="nombre_cliente"
                                                class="block w-full text-xs sm:text-sm bg-purple-500 text-white rounded-md py-2 px-3 shadow-sm"
                                                placeholder="Proveedor seleccionado" readonly>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>

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
                            <input wire:model.debounce.300ms="searchArticulo" type="text" id="articulo"
                                class="block w-full text-sm sm:text-base border border-gray-300 rounded-md py-2 px-3 text-black shadow-purple-200 focus:ring-purple-500 focus:border-purple-500"
                                placeholder="Escanear o ingresar código" autofocus>
                            @if ($searchArticulo)
                                <div class="absolute z-50 bg-white w-full rounded-md shadow-lg mt-1">
                                    @foreach ($articulo as $opcion)
                                        <div wire:click="agregarArticulo({{ $opcion['idarticulo'] }})"
                                            class="py-2 px-3 cursor-pointer hover:bg-gray-100 text-sm">
                                            {{ $opcion['nombre'] }} | {{ $opcion['descripcion'] }}
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        <!-- Selector de variantes -->
                        @if ($mostrar_variantes && count($variantes_disponibles) > 0)
                            <div class="bg-gray-100 border rounded-md shadow-md p-3 sm:p-4 mb-3 sm:mb-4">
                                <h4 class="text-sm sm:text-base font-semibold text-gray-800 mb-2 sm:mb-3">Selecciona una
                                    variante</h4>
                                <ul class="space-y-2">
                                    @foreach ($variantes_disponibles as $variante)
                                        <li>
                                            <button wire:click="seleccionarVariante({{ $variante->id_variante }})"
                                                class="w-full flex justify-between items-center px-3 sm:px-4 py-2 bg-white hover:bg-purple-100 border rounded-md shadow-sm transition text-xs sm:text-sm">
                                                <span
                                                    class="flex-1 text-left pr-2">{{ $variante->descripcion_variante }}
                                                    (Stock: {{ $variante->stock }})
                                                </span>
                                                <span class="font-bold text-purple-600 whitespace-nowrap">SKU:
                                                    {{ $variante->sku }}</span>
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
                                            class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-[25%]">
                                            Producto</th>
                                        <th scope="col"
                                            class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-[8%]">
                                            Stock Actual</th>
                                        <th scope="col"
                                            class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-[12%]">
                                            Precio Compra</th>
                                        <th scope="col"
                                            class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-[10%]">
                                            % Ganancia</th>
                                        <th scope="col"
                                            class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-[12%]">
                                            Precio Venta</th>
                                        <th scope="col"
                                            class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-[12%]">
                                            Cantidad</th>
                                        <th scope="col"
                                            class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-[8%]">
                                            Nuevo Stock</th>
                                        <th scope="col"
                                            class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-[10%]">
                                            Subtotal</th>
                                        <th scope="col"
                                            class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-[5%]">
                                            Acción</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($articuloSeleccionado as $index => $art)
                                        @if (!isset($art['eliminado']) || !$art['eliminado'])
                                            <!-- Mobile: Card Design / Desktop: Table Row -->
                                            <tr
                                                class="hover:bg-gray-50 md:table-row border-b md:border-none bg-white md:bg-transparent">
                                                <td colspan="9" class="md:contents">
                                                    <!-- Mobile Card - (ya está bien, no lo toques) -->
                                                    <div
                                                        class="md:hidden p-2 border-b border-gray-200 last:border-b-0 bg-white hover:bg-gray-50 transition-colors">
                                                        <!-- Nombre del Producto -->
                                                        <div class="mb-2">
                                                            <p class="text-sm font-semibold text-gray-900">
                                                                {{ $art['nombre'] ?? '' }}</p>
                                                            <p class="text-xs text-gray-500">Stock Actual:
                                                                {{ $art['stock_original'] ?? ($art['stock'] ?? '') }}
                                                            </p>
                                                        </div>

                                                        <!-- Grid de 3 columnas para inputs -->
                                                        <div class="grid grid-cols-3 gap-2 mb-2">
                                                            <!-- Precio Compra -->
                                                            <div>
                                                                <label class="block text-xs text-gray-600 mb-1">P.
                                                                    Compra</label>
                                                                <div class="relative">
                                                                    <span
                                                                        class="absolute left-1.5 top-1.5 text-gray-500 text-xs">$</span>
                                                                    <input type="number"
                                                                        wire:model="articuloSeleccionado.{{ $index }}.precio_compra"
                                                                        wire:change="calcularSubTotalProducto({{ $index }})"
                                                                        class="w-full pl-5 pr-1 py-1.5 text-xs border-gray-300 rounded focus:ring-purple-500 focus:border-purple-500 text-right"
                                                                        step="0.01" placeholder="0.00" />
                                                                </div>
                                                            </div>

                                                            <!-- % Ganancia -->
                                                            <div>
                                                                <label class="block text-xs text-gray-600 mb-1">%
                                                                    Gan.</label>
                                                                <div class="relative">
                                                                    <input type="number"
                                                                        wire:model="articuloSeleccionado.{{ $index }}.porcentaje_ganancia"
                                                                        wire:change="calcularSubTotalProducto({{ $index }})"
                                                                        class="w-full pr-5 pl-1 py-1.5 text-xs border-gray-300 rounded focus:ring-purple-500 focus:border-purple-500 text-right"
                                                                        step="0.01" placeholder="0" />
                                                                    <span
                                                                        class="absolute right-1.5 top-1.5 text-gray-500 text-xs">%</span>
                                                                </div>
                                                            </div>

                                                            <!-- Precio Venta -->
                                                            <div>
                                                                <label class="block text-xs text-gray-600 mb-1">P.
                                                                    Venta</label>
                                                                <div class="relative">
                                                                    <span
                                                                        class="absolute left-1.5 top-1.5 text-gray-500 text-xs">$</span>
                                                                    <input type="number"
                                                                        wire:model="articuloSeleccionado.{{ $index }}.precio_venta"
                                                                        class="w-full pl-5 pr-1 py-1.5 text-xs border-gray-300 rounded focus:ring-purple-500 focus:border-purple-500 text-right bg-green-50"
                                                                        step="0.01" placeholder="0.00" readonly />
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- Fila con Cantidad, Nuevo Stock, Subtotal y Eliminar -->
                                                        <div class="flex items-center gap-2">
                                                            <!-- Cantidad -->
                                                            <div class="flex items-center">
                                                                <button type="button"
                                                                    wire:click="decrementarCantidad({{ $index }})"
                                                                    class="bg-gray-200 w-7 h-7 rounded-l flex items-center justify-center hover:bg-gray-300">
                                                                    <svg class="w-3.5 h-3.5" fill="none"
                                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path d="M20 12H4" stroke-width="2.5"
                                                                            stroke-linecap="round"
                                                                            stroke-linejoin="round" />
                                                                    </svg>
                                                                </button>
                                                                <input type="number"
                                                                    wire:model="articuloSeleccionado.{{ $index }}.cantidad"
                                                                    wire:change="calcularSubTotalProducto({{ $index }})"
                                                                    class="w-10 text-center text-sm font-medium border-y border-gray-300 focus:ring-0 focus:outline-none"
                                                                    min="1">
                                                                <button type="button"
                                                                    wire:click="incrementarCantidad({{ $index }})"
                                                                    class="bg-gray-200 w-7 h-7 rounded-r flex items-center justify-center hover:bg-gray-300">
                                                                    <svg class="w-3.5 h-3.5" fill="none"
                                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path d="M12 4v16m8-8H4" stroke-width="2.5"
                                                                            stroke-linecap="round"
                                                                            stroke-linejoin="round" />
                                                                    </svg>
                                                                </button>
                                                            </div>

                                                            <!-- Nuevo Stock -->
                                                            <div class="flex-1 text-center">
                                                                <p class="text-xs text-gray-500">Nuevo Stock</p>
                                                                <p class="text-sm font-semibold text-green-600">
                                                                    {{ $art['stock'] ?? '' }}</p>
                                                            </div>

                                                            <!-- Subtotal -->
                                                            <div class="flex-1 text-right">
                                                                <p class="text-xs text-gray-500">Subtotal</p>
                                                                <p class="text-sm font-semibold text-gray-900">
                                                                    ${{ isset($art['subtotal']) ? number_format($art['subtotal'], 2) : '0.00' }}
                                                                </p>
                                                            </div>

                                                            <!-- Botón Eliminar -->
                                                            <button wire:click="eliminarArticulo({{ $index }})"
                                                                class="flex-shrink-0 p-1.5 rounded-md text-red-600 hover:bg-red-50">
                                                                <svg class="w-4 h-4" fill="none"
                                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path d="M6 18L18 6M6 6l12 12"
                                                                        stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="2" />
                                                                </svg>
                                                            </button>
                                                        </div>
                                                    </div>

                                                    <!-- Desktop Table Cells -->
                                                    <!-- Producto -->
                                                <td class="hidden md:table-cell py-2 px-3">
                                                    <span
                                                        class="text-gray-900 text-xs leading-tight block max-w-[200px]">{{ $art['nombre'] ?? '' }}</span>
                                                </td>

                                                <!-- Stock Actual -->
                                                <td class="hidden md:table-cell py-2 px-3 text-center">
                                                    <span
                                                        class="text-gray-900 text-sm">{{ $art['stock_original'] ?? ($art['stock'] ?? '') }}</span>
                                                </td>

                                                <!-- Precio Compra -->
                                                <td class="hidden md:table-cell py-2 px-2">
                                                    <div class="relative">
                                                        <div
                                                            class="absolute inset-y-0 left-0 pl-1.5 flex items-center pointer-events-none">
                                                            <span class="text-gray-500 text-xs">$</span>
                                                        </div>
                                                        <input type="number"
                                                            wire:model="articuloSeleccionado.{{ $index }}.precio_compra"
                                                            wire:change="calcularSubTotalProducto({{ $index }})"
                                                            class="pl-5 pr-1.5 py-1.5 w-full focus:ring-purple-500 focus:border-purple-500 block text-xs border-gray-300 rounded-md text-right"
                                                            step="0.01" placeholder="0.00" />
                                                    </div>
                                                </td>

                                                <!-- % Ganancia -->
                                                <td class="hidden md:table-cell py-2 px-2">
                                                    <div class="relative">
                                                        <input type="number"
                                                            wire:model="articuloSeleccionado.{{ $index }}.porcentaje_ganancia"
                                                            wire:change="calcularSubTotalProducto({{ $index }})"
                                                            class="pr-5 pl-1.5 py-1.5 w-full focus:ring-purple-500 focus:border-purple-500 block text-xs border-gray-300 rounded-md text-right"
                                                            step="0.01" placeholder="0" />
                                                        <div
                                                            class="absolute inset-y-0 right-0 pr-1.5 flex items-center pointer-events-none">
                                                            <span class="text-gray-500 text-xs">%</span>
                                                        </div>
                                                    </div>
                                                </td>

                                                <!-- Precio Venta -->
                                                <td class="hidden md:table-cell py-2 px-2">
                                                    <div class="relative">
                                                        <div
                                                            class="absolute inset-y-0 left-0 pl-1.5 flex items-center pointer-events-none">
                                                            <span class="text-gray-500 text-xs">$</span>
                                                        </div>
                                                        <input type="number"
                                                            wire:model="articuloSeleccionado.{{ $index }}.precio_venta"
                                                            class="pl-5 pr-1.5 py-1.5 w-full focus:ring-purple-500 focus:border-purple-500 block text-xs border-gray-300 rounded-md text-right bg-green-50"
                                                            step="0.01" placeholder="0.00" readonly />
                                                    </div>
                                                </td>

                                                <!-- Cantidad -->
                                                <td class="hidden md:table-cell py-2 px-2">
                                                    <div class="flex items-center justify-center">
                                                        <button type="button"
                                                            wire:click="decrementarCantidad({{ $index }})"
                                                            class="bg-gray-200 px-1.5 py-1 rounded-l-md hover:bg-gray-300 transition">
                                                            <svg class="w-3.5 h-3.5" fill="none"
                                                                stroke="currentColor" viewBox="0 0 24 24">
                                                                <path d="M20 12H4" stroke-width="2"
                                                                    stroke-linecap="round" stroke-linejoin="round" />
                                                            </svg>
                                                        </button>
                                                        <input type="number"
                                                            wire:model="articuloSeleccionado.{{ $index }}.cantidad"
                                                            wire:change="calcularSubTotalProducto({{ $index }})"
                                                            class="text-center w-12 px-1 py-1 border-y border-gray-300 focus:ring-purple-500 focus:border-purple-500 text-xs"
                                                            min="1">
                                                        <button type="button"
                                                            wire:click="incrementarCantidad({{ $index }})"
                                                            class="bg-gray-200 px-1.5 py-1 rounded-r-md hover:bg-gray-300 transition">
                                                            <svg class="w-3.5 h-3.5" fill="none"
                                                                stroke="currentColor" viewBox="0 0 24 24">
                                                                <path d="M12 4v16m8-8H4" stroke-width="2"
                                                                    stroke-linecap="round" stroke-linejoin="round" />
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </td>

                                                <!-- Nuevo Stock -->
                                                <td class="hidden md:table-cell py-2 px-3 text-center">
                                                    <span
                                                        class="text-sm font-semibold text-green-600">{{ $art['stock'] ?? '' }}</span>
                                                </td>

                                                <!-- Subtotal -->
                                                <td class="hidden md:table-cell py-2 px-3 text-right">
                                                    <span class="font-medium text-gray-900 text-sm whitespace-nowrap">
                                                        ${{ isset($art['subtotal']) ? number_format($art['subtotal'], 2) : '0.00' }}
                                                    </span>
                                                </td>

                                                <!-- Acción -->
                                                <td class="hidden md:table-cell py-2 px-2">
                                                    <div class="flex justify-center">
                                                        <button wire:click="eliminarArticulo({{ $index }})"
                                                            class="inline-flex items-center justify-center p-1 border border-transparent rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                                viewBox="0 0 24 24">
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
                        <h3 class="text-base sm:text-lg font-medium text-gray-900 mb-3 sm:mb-4">DETALLE COMPRA</h3>
                        <div class="grid grid-cols-2 gap-2 sm:gap-4 mb-3 sm:mb-4">
                            <div>
                                <label for="descuento"
                                    class="block text-xs sm:text-sm font-medium text-gray-700 mb-1.5 sm:mb-2">Descuento</label>
                                <input type="number" id="descuento" wire:model.debounce.500ms="descuento"
                                    class="block w-full text-sm sm:text-base border-gray-300 rounded-md py-2 px-2 sm:px-3 text-gray-700 shadow-purple-200 focus:ring-purple-500 focus:border-purple-500"
                                    placeholder="%">
                            </div>
                            <div>
                                <label for="recargo"
                                    class="block text-xs sm:text-sm font-medium text-gray-700 mb-1.5 sm:mb-2">Recargo</label>
                                <input type="number" id="recargo" wire:model.debounce.500ms="recargo"
                                    class="block w-full text-sm sm:text-base border-gray-300 rounded-md py-2 px-2 sm:px-3 text-gray-700 shadow-purple-200 focus:ring-purple-500 focus:border-purple-500"
                                    placeholder="%">
                            </div>
                        </div>
                        <div class="mb-3 sm:mb-4">
                            <label for="total"
                                class="block text-base sm:text-lg font-semibold text-gray-700 mb-1.5 sm:mb-2">Total</label>
                            <div
                                class="block w-full text-2xl sm:text-3xl font-bold text-purple-600 bg-purple-100 border border-purple-300 rounded-md py-3 sm:py-4 px-4 sm:px-6 shadow-lg">
                                ${{ number_format($compra_total ?? 0, 2) }}
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4 mb-4 sm:mb-6">
                            <div>
                                <label for="pago"
                                    class="block text-xs sm:text-sm font-medium text-gray-700 mb-1.5 sm:mb-2">Pago</label>
                                <input type="number" id="pago" wire:model.debounce.500ms="pago"
                                    wire:change="calcularSaldo()"
                                    class="block w-full text-sm sm:text-base border-gray-300 rounded-md py-2 px-2 sm:px-3 text-gray-700 shadow-purple-200 focus:ring-purple-500 focus:border-purple-500"
                                    placeholder="$0.00" step="0.01">
                            </div>
                            <div>
                                <label for="cambio" class="block text-xs sm:text-sm font-medium mb-1.5 sm:mb-2"
                                    :class="{ 'text-green-600': $wire.saldo < 0, 'text-red-600': $wire.saldo >= 0 }">
                                    <span x-text="$wire.saldo < 0 ? 'Cambio' : 'Saldo'"></span>
                                </label>
                                <span
                                    class="block w-full px-2 sm:px-3 py-2 text-base sm:text-lg font-semibold rounded-md text-center"
                                    :class="{
                                        'text-green-600 bg-green-100': $wire.saldo < 0,
                                        'text-red-600 bg-red-100': $wire.saldo >= 0
                                    }"
                                    x-text="'$' + Math.abs($wire.saldo || 0).toFixed(2)" wire:model="saldo">
                                </span>
                            </div>
                        </div>
                        <button wire:click="guardar()"
                            class="w-full bg-purple-600 text-white py-2.5 sm:py-3 px-4 sm:px-6 rounded-lg shadow-lg transition-all duration-300 ease-in-out transform hover:scale-105 hover:bg-purple-700 focus:outline-none focus:ring-4 focus:ring-purple-400">
                            <span class="font-semibold text-base sm:text-lg tracking-wide">Cargar Compra</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Componente de Artículos embebido -->
@livewire('articulo.articulo-livewire', ['layout' => 'compras'])
