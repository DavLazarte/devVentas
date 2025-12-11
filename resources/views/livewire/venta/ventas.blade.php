<div class="min-h-screen bg-gray-50">
    <!-- HEADER FIJO SUPERIOR -->
    <div class="top-0 z-40 bg-white shadow-lg border-b-2 border-purple-200">
        <div class="max-w-full mx-auto px-2 sm:px-3 py-1">
            <!-- Título y Mensaje -->
            <div class="flex items-center justify-between mb-1">
                <h3 class="text-base sm:text-lg font-bold text-gray-900">PUNTO DE VENTA</h3>
                @if ($mensajeVenta)
                    <div class="bg-teal-100 rounded px-3 sm:px-4 py-2 shadow-md" role="alert">
                        <span class="text-teal-900 font-medium text-sm">{{ $mensajeVenta }}</span>
                    </div>
                @endif
            </div>

            <!-- FILA 1: Configuraciones de la venta -->
            <div class="grid grid-cols-12 gap-2 sm:gap-3 mb-2">
                <!-- Buscar Producto -->
                <div class="col-span-12 md:col-span-6 lg:col-span-4 relative">
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-xs font-medium text-gray-700">Buscar Producto</label>
                        <button type="button" wire:click="$emit('abrirModalCrearArticulo')"
                            class="inline-flex items-center gap-1 text-xs font-medium text-purple-600 hover:text-purple-800">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v16m8-8H4" />
                            </svg>
                            Nuevo
                        </button>
                    </div>
                    <input wire:model.debounce.300ms="searchArticulo" type="text"
                        class="block w-full text-sm border border-gray-300 rounded-md py-1.5 px-3 focus:ring-purple-500 focus:border-purple-500"
                        placeholder="Escanear o buscar..." autofocus>
                    @if ($searchArticulo)
                        <div class="absolute z-50 bg-white w-full rounded-md shadow-lg mt-1 max-h-60 overflow-y-auto">
                            @foreach ($articulo as $opcion)
                                <div wire:click="agregarArticulo({{ $opcion['idarticulo'] }})"
                                    class="py-2 px-3 cursor-pointer hover:bg-purple-50 border-b">
                                    <span class="font-medium text-sm">{{ $opcion['nombre'] }}</span>
                                    <span class="text-xs text-gray-500">| {{ $opcion['descripcion'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Tipo de Venta -->
                <div class="col-span-6 md:col-span-3 lg:col-span-2">
                    <label class="block text-xs font-medium text-gray-700 mb-2">Tipo de Venta</label>
                    <div class="grid grid-cols-1 gap-2">
                        <label class="relative cursor-pointer">
                            <input type="radio" name="tipo_venta" value="venta_rapida" wire:model="tipo_venta"
                                class="peer sr-only">
                            <div
                                class="w-full px-2 py-1 bg-white border-2 rounded-md border-gray-200 text-gray-600 peer-checked:border-purple-500 peer-checked:bg-purple-50 peer-checked:text-purple-700 hover:border-gray-300 text-xs font-medium text-center transition-all">
                                Venta Rápida
                            </div>
                        </label>
                        <label class="relative cursor-pointer">
                            <input type="radio" name="tipo_venta" value="cuenta_corriente" wire:model="tipo_venta"
                                class="peer sr-only">
                            <div
                                class="w-full px-2 py-1 bg-white border-2 rounded-md border-gray-200 text-gray-600 peer-checked:border-purple-500 peer-checked:bg-purple-50 peer-checked:text-purple-700 hover:border-gray-300 text-xs font-medium text-center transition-all">
                                Cuenta Corriente
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Cliente - Solo si es cuenta corriente -->
                @if ($tipo_venta === 'cuenta_corriente')
                    <div class="col-span-6 md:col-span-3 lg:col-span-3 relative">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Cliente</label>
                        <input type="text" wire:model.debounce.300ms="searchCliente"
                            class="block w-full text-sm border border-gray-300 rounded-md py-1.5 px-3 focus:ring-purple-500 focus:border-purple-500"
                            placeholder="Buscar cliente...">
                        @if ($searchCliente)
                            <div
                                class="absolute z-50 bg-white w-full rounded-md shadow-lg mt-1 max-h-40 overflow-y-auto">
                                @foreach ($persona as $opcion)
                                    <div wire:click="agregarCliente({{ $opcion['idpersona'] }})"
                                        class="py-2 px-3 cursor-pointer hover:bg-purple-50 text-sm">
                                        {{ $opcion['nombre'] }}
                                    </div>
                                @endforeach
                            </div>
                        @endif
                        @if ($clienteSeleccionado)
                            <div class="mt-1">
                                <input type="text" wire:model="nombre_cliente"
                                    class="block w-full text-xs bg-purple-500 text-white rounded py-1 px-2"
                                    placeholder="Cliente seleccionado" readonly>
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Forma de Pago -->
                <div class="col-span-12 md:col-span-6 lg:col-span-3">
                    <label class="block text-xs font-medium text-gray-700 mb-1">Forma de Pago</label>
                    <select wire:model="forma_de_pago"
                        class="block w-full text-sm border-gray-300 rounded-md py-1.5 px-3 focus:ring-purple-500 focus:border-purple-500">
                        <option value="">
                            Seleccionar forma de pago
                        </option>
                        <option value="efectivo">💵 Efectivo</option>
                        <option value="transferencia">🏦 Transferencia</option>
                        <option value="tarjeta">💳 Tarjeta</option>
                        <option value="cuenta_corriente">📋 A cuenta</option>
                    </select>
                </div>
            </div>

            <!-- FILA 2: Totales y Acciones (MÁS GRANDE) -->
            <div
                class="grid grid-cols-12 gap-2 bg-gradient-to-r from-purple-50 to-blue-50 p-1 rounded-lg border border-purple-200">
                <!-- Descuento -->
                <div class="col-span-6 sm:col-span-4 md:col-span-3 lg:col-span-2">
                    <label class="block text-[10px] font-semibold text-gray-700 mb-0.5">Descuento %</label>
                    <input type="number" wire:model.debounce.500ms="descuento"
                        class="block w-full text-xs border-gray-300 rounded-md py-0.5 px-1 focus:ring-purple-500 focus:border-purple-500 font-medium"
                        placeholder="0">
                </div>

                <!-- Recargo -->
                <div class="col-span-6 sm:col-span-4 md:col-span-3 lg:col-span-2">
                    <label class="block text-[10px] font-semibold text-gray-700 mb-0.5">Recargo %</label>
                    <input type="number" wire:model.debounce.500ms="recargo"
                        class="block w-full text-xs border-gray-300 rounded-md py-0.5 px-1 focus:ring-purple-500 focus:border-purple-500 font-medium"
                        placeholder="0">
                </div>

                <!-- TOTAL (MÁS GRANDE) -->
                <div class="col-span-12 sm:col-span-4 md:col-span-6 lg:col-span-3">
                    <label class="block text-[10px] font-bold text-gray-700 mb-0.5">TOTAL</label>
                    <div
                        class="text-lg sm:text-xl font-black text-purple-600 bg-white border border-purple-400 rounded-md py-0.5 px-1 text-center shadow-sm">
                        ${{ number_format($venta_total, 2) }}
                    </div>
                </div>

                <!-- Pago -->
                <div class="col-span-6 sm:col-span-6 md:col-span-4 lg:col-span-2">
                    <label class="block text-[10px] font-semibold text-gray-700 mb-0.5">Pago</label>
                    <input type="number" wire:model.debounce.500ms="pago"
                        class="block w-full text-xs border-gray-300 rounded-md py-0.5 px-1 focus:ring-purple-500 focus:border-purple-500 font-medium"
                        placeholder="0.00">
                </div>

                <!-- Saldo/Cambio (MÁS GRANDE) -->
                <div class="col-span-6 sm:col-span-6 md:col-span-4 lg:col-span-2">
                    <label class="block text-[10px] font-bold mb-0.5"
                        :class="{ 'text-green-700': $wire.saldo < 0, 'text-red-700': $wire.saldo >= 0 }">
                        <span x-text="$wire.saldo < 0 ? 'CAMBIO' : 'SALDO'"></span>
                    </label>
                    <div class="text-sm sm:text-base font-bold rounded-md py-0.5 px-1 text-center border"
                        :class="{
                            'text-green-700 bg-green-50 border-green-400': $wire.saldo < 0,
                            'text-red-700 bg-red-50 border-red-400': $wire.saldo >= 0
                        }"
                        x-text="'$' + Math.abs($wire.saldo).toFixed(2)">
                    </div>
                </div>

                <!-- Botón Cargar Venta (MÁS GRANDE) -->
                <div class="col-span-12 md:col-span-4 lg:col-span-1">
                    <label class="block text-[10px] font-medium text-transparent mb-0.5">.</label>
                    <button wire:click="guardar()"
                        class="w-full bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800 text-white py-1 px-2 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-purple-400 transition-all text-xs font-bold transform hover:scale-105">
                        CARGAR
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Selector de variantes -->
    @if ($mostrar_variantes && count($variantes_disponibles) > 0)
        <div class="max-w-full mx-auto px-3 sm:px-4 py-4">
            <div
                class="bg-gradient-to-r from-purple-50 to-blue-50 border-2 border-purple-300 rounded-lg shadow-lg p-4">
                <h4 class="text-base font-bold text-gray-800 mb-3">Selecciona una variante</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3">
                    @foreach ($variantes_disponibles as $variante)
                        <button wire:click="seleccionarVariante({{ $variante->id_variante }})"
                            class="flex justify-between items-center px-4 py-3 bg-white hover:bg-purple-100 border-2 border-purple-200 hover:border-purple-400 rounded-lg shadow-sm transition-all text-sm">
                            <div class="text-left flex-1">
                                <div class="font-semibold text-gray-900">{{ $variante->descripcion_variante }}</div>
                                <div class="text-xs text-gray-500">
                                    Stock:
                                    @if ($variante->tipo_venta === 'peso' || $variante->tipo_venta === 'volumen')
                                        {{ $variante->stock_decimal ?? 0 }} {{ $variante->unidad_medida ?? 'kg' }}
                                    @else
                                        {{ $variante->stock }}
                                    @endif
                                </div>
                            </div>
                            <span
                                class="font-bold text-purple-600 ml-3">${{ number_format($variante->precio_unitario, 2) }}</span>
                        </button>
                    @endforeach
                </div>
                <button wire:click="cerrarSelectorVariantes"
                    class="mt-3 text-sm text-red-600 hover:text-red-800 hover:underline font-medium">
                    Cancelar
                </button>
            </div>
        </div>
    @endif

    <!-- TABLA DE PRODUCTOS - RESPONSIVE (Cards en Mobile, Tabla en Desktop) -->
    <div class="max-w-full mx-auto px-2 sm:px-4 py-4 pb-20">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="w-full">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gradient-to-r from-purple-600 to-purple-700 hidden sm:table-header-group">
                        <tr>
                            <th
                                class="px-3 sm:px-4 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">
                                Producto</th>
                            <th
                                class="px-3 sm:px-4 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">
                                Stock</th>
                            <th
                                class="px-3 sm:px-4 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">
                                Precio</th>
                            <th
                                class="px-3 sm:px-4 py-3 text-center text-xs font-bold text-white uppercase tracking-wider">
                                Cantidad</th>
                            <th
                                class="px-3 sm:px-4 py-3 text-right text-xs font-bold text-white uppercase tracking-wider">
                                Subtotal</th>
                            <th
                                class="px-3 sm:px-4 py-3 text-center text-xs font-bold text-white uppercase tracking-wider w-16 sm:w-20">
                                Acción</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($articuloSeleccionado as $index => $art)
                            @if (!isset($art['eliminado']) || !$art['eliminado'])
                                <tr
                                    class="hover:bg-purple-50 transition-colors block sm:table-row border-b-4 border-gray-100 sm:border-b last:border-0 mb-2 sm:mb-0">

                                    <!-- Producto (Mobile: Header de Card) -->
                                    <td
                                        class="px-3 sm:px-4 py-2 sm:py-3 block sm:table-cell bg-purple-50 sm:bg-transparent">
                                        <div class="flex justify-between items-start sm:block">
                                            <div>
                                                <div class="text-sm font-bold sm:font-semibold text-gray-900">
                                                    {{ $art['nombre'] ?? '' }}</div>
                                                @if ($art['descripcion_variante'] ?? false)
                                                    <div class="text-xs text-gray-500">
                                                        {{ $art['descripcion_variante'] }}</div>
                                                @endif
                                            </div>
                                            <!-- Mobile Delete Button visible in header of card -->
                                            <button wire:click="eliminarArticulo({{ $index }})"
                                                class="sm:hidden text-red-500 p-1">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path d="M6 18L18 6M6 6l12 12" stroke-linecap="round"
                                                        stroke-linejoin="round" stroke-width="2" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>

                                    <!-- Stock -->
                                    <td
                                        class="px-3 sm:px-4 py-2 sm:py-3 block sm:table-cell flex justify-between items-center sm:block">
                                        <span
                                            class="sm:hidden text-xs font-semibold text-gray-500 uppercase">Stock:</span>
                                        <span class="text-sm text-gray-700 font-medium">
                                            {{ $art['stock'] ?? '' }}
                                            @if ($art['permite_decimales'] ?? false)
                                                <span
                                                    class="text-xs text-gray-500">{{ $art['unidad_medida'] ?? '' }}</span>
                                            @endif
                                        </span>
                                    </td>

                                    <!-- Precio -->
                                    <td
                                        class="px-3 sm:px-4 py-2 sm:py-3 block sm:table-cell flex justify-between items-center sm:block">
                                        <span
                                            class="sm:hidden text-xs font-semibold text-gray-500 uppercase">Precio:</span>
                                        <div class="relative w-32 sm:w-28">
                                            <span
                                                class="absolute left-2 top-1/2 transform -translate-y-1/2 text-gray-500 text-sm">$</span>
                                            <input type="number"
                                                wire:model="articuloSeleccionado.{{ $index }}.precio_unitario"
                                                wire:change="calcularSubTotalProducto({{ $index }})"
                                                step="0.01"
                                                class="pl-6 pr-2 py-1.5 w-full text-sm border-gray-300 rounded-md focus:ring-purple-500 focus:border-purple-500 text-right font-semibold">
                                        </div>
                                    </td>

                                    <!-- Cantidad -->
                                    <td
                                        class="px-3 sm:px-4 py-2 sm:py-3 block sm:table-cell flex justify-between items-center sm:block">
                                        <span
                                            class="sm:hidden text-xs font-semibold text-gray-500 uppercase">Cantidad:</span>
                                        <div class="flex justify-end sm:justify-center w-full sm:w-auto">
                                            @if ($art['permite_decimales'] ?? false)
                                                <div class="flex items-center gap-2">
                                                    <input type="number"
                                                        wire:model="articuloSeleccionado.{{ $index }}.cantidad"
                                                        wire:change="calcularSubTotalProducto({{ $index }})"
                                                        step="0.001" min="0.001"
                                                        class="w-24 text-center px-2 py-1.5 border-gray-300 focus:ring-purple-500 focus:border-purple-500 text-sm rounded-md"
                                                        placeholder="1.500">
                                                    <span
                                                        class="text-xs text-gray-500">{{ $art['unidad_medida'] ?? '' }}</span>
                                                </div>
                                            @else
                                                <div class="flex items-center gap-2">
                                                    <button type="button"
                                                        wire:click="decrementarCantidad({{ $index }})"
                                                        class="bg-purple-100 hover:bg-purple-200 text-purple-700 w-8 h-8 rounded-full flex items-center justify-center transition-colors">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path d="M20 12H4" stroke-width="2.5"
                                                                stroke-linecap="round" stroke-linejoin="round" />
                                                        </svg>
                                                    </button>
                                                    <input type="number"
                                                        wire:model="articuloSeleccionado.{{ $index }}.cantidad"
                                                        wire:change="calcularSubTotalProducto({{ $index }})"
                                                        min="1"
                                                        class="w-16 text-center px-1 py-1.5 border-gray-300 focus:ring-purple-500 focus:border-purple-500 text-sm rounded-md font-bold text-gray-800">
                                                    <button type="button"
                                                        wire:click="incrementarCantidad({{ $index }})"
                                                        class="bg-purple-100 hover:bg-purple-200 text-purple-700 w-8 h-8 rounded-full flex items-center justify-center transition-colors">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path d="M12 4v16m8-8H4" stroke-width="2.5"
                                                                stroke-linecap="round" stroke-linejoin="round" />
                                                        </svg>
                                                    </button>
                                                </div>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- Subtotal -->
                                    <td
                                        class="px-3 sm:px-4 py-2 sm:py-3 block sm:table-cell flex justify-between items-center sm:block bg-gray-50 sm:bg-transparent">
                                        <span
                                            class="sm:hidden text-xs font-bold text-gray-600 uppercase">Subtotal:</span>
                                        <span class="text-base sm:text-sm font-bold text-gray-900">
                                            ${{ isset($art['subtotal']) ? number_format($art['subtotal'], 2) : '0.00' }}
                                        </span>
                                    </td>

                                    <!-- Acción (Desktop Only - Mobile is in Header) -->
                                    <td class="hidden sm:table-cell px-3 sm:px-4 py-3 text-center">
                                        <button wire:click="eliminarArticulo({{ $index }})"
                                            class="inline-flex items-center justify-center p-2 rounded-md text-red-600 hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500 transition-colors">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path d="M6 18L18 6M6 6l12 12" stroke-linecap="round"
                                                    stroke-linejoin="round" stroke-width="2" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            @endif
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-12 text-center block sm:table-cell">
                                    <div class="flex flex-col items-center justify-center text-gray-400">
                                        <svg class="w-16 h-16 mb-3" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                        </svg>
                                        <p class="text-lg font-medium">No hay productos en el carrito</p>
                                        <p class="text-sm mt-1">Busca y agrega productos para comenzar la venta</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
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
                    confirmButton: 'bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700',
                }
            });
        });
    </script>
@endpush

@livewire('articulo.articulo-livewire', ['layout' => 'ventas'])
