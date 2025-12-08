<div>
    <!-- Header -->
    <header class="sticky top-0 z-10 bg-white shadow-sm border-b border-gray-200">
        <div class="container mx-auto px-4 py-4 flex items-center justify-between">
            <h1 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                @if ($tipo_pedido === 'servicio')
                    Editar Reserva
                @else
                    Editar Pedido
                @endif
                <span
                    class="px-2.5 py-0.5 rounded-full text-sm font-medium
                    @if ($estado === 'pendiente') bg-orange-100 text-orange-700
                    @elseif($estado === 'en_proceso') bg-yellow-100 text-yellow-700
                    @elseif($estado === 'confirmado') bg-blue-100 text-blue-700
                    @elseif($estado === 'entregado' || $estado === 'realizado') bg-green-100 text-green-700
                    @elseif($estado === 'cancelado') bg-red-100 text-red-700 @endif">
                    {{ ucfirst($estado) }}
                </span>
            </h1>

            <button wire:click="closeModal" class="p-2 rounded-full hover:bg-gray-100 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-500" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </header>

    <main class="pb-24 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- Right Column: Actions & Info (Order 1 on mobile to show actions first) -->
                <div class="lg:col-span-1 order-1 lg:order-2 space-y-6">

                    <!-- Status Update Card -->
                    <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-purple-100">
                        <div class="px-6 py-4 border-b border-gray-100 bg-purple-50">
                            <h2 class="text-lg font-bold text-purple-900">Gestionar Estado</h2>
                        </div>
                        <div class="p-6">
                            <form wire:submit.prevent="guardar" class="space-y-4">
                                <div>
                                    <label for="estado" class="block text-sm font-medium text-gray-700 mb-1">Nuevo
                                        Estado</label>
                                    <div class="relative">
                                        <select id="estado" wire:model="estado"
                                            class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-purple-500 focus:border-purple-500 sm:text-sm rounded-md">
                                            @if ($tipo_pedido === 'producto')
                                                <option value="pendiente">🟠 Pendiente</option>
                                                <option value="en_proceso">🟡 En Proceso</option>
                                                <option value="confirmado">🔵 Confirmado</option>
                                                <option value="entregado">🟢 Entregado</option>
                                                <option value="cancelado">🔴 Cancelado</option>
                                            @elseif($tipo_pedido === 'servicio')
                                                <option value="pendiente">🟠 Pendiente</option>
                                                <option value="confirmada">🔵 Confirmado</option>
                                                <option value="completada">🟢 Realizado</option>
                                                <option value="cancelada">🔴 Cancelado</option>
                                            @endif
                                        </select>
                                    </div>
                                    @error('estado')
                                        <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span>
                                    @enderror
                                </div>

                                <button type="submit"
                                    class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-colors">
                                    Guardar Cambios
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Order Summary Card -->
                    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100">
                            <h2 class="text-lg font-bold text-gray-900">Resumen</h2>
                        </div>
                        <div class="p-6 space-y-3">
                            <div class="flex justify-between text-gray-600">
                                <span>Subtotal</span>
                                <span class="font-medium">${{ number_format($subtotal, 2) }}</span>
                            </div>
                            <div class="flex justify-between text-gray-600">
                                <span>Envío</span>
                                <span class="font-medium">${{ number_format($envio, 2) }}</span>
                            </div>
                            @if ($descuento > 0)
                                <div class="flex justify-between text-green-600">
                                    <span>Descuento</span>
                                    <span class="font-medium">-${{ number_format($descuento, 2) }}</span>
                                </div>
                            @endif
                            <div class="border-t border-gray-100 pt-3 mt-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-lg font-bold text-gray-900">Total</span>
                                    <span
                                        class="text-2xl font-bold text-purple-600">${{ number_format($total, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Customer Info -->
                    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100">
                            <h2 class="text-lg font-bold text-gray-900">Cliente</h2>
                        </div>
                        <div class="p-6 space-y-4">
                            <div class="flex items-start space-x-3">
                                <div class="bg-gray-100 p-2 rounded-lg">
                                    <svg class="h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Nombre</p>
                                    <p class="font-medium text-gray-900">{{ $nombre_cliente }}</p>
                                </div>
                            </div>
                            <div class="flex items-start space-x-3">
                                <div class="bg-gray-100 p-2 rounded-lg">
                                    <svg class="h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Email</p>
                                    <p class="font-medium text-gray-900">{{ $email }}</p>
                                </div>
                            </div>
                            <div class="flex items-start space-x-3">
                                <div class="bg-gray-100 p-2 rounded-lg">
                                    <svg class="h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Teléfono</p>
                                    <p class="font-medium text-gray-900">{{ $telefono }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Left Column: Order Items (Order 2 on mobile) -->
                <div class="lg:col-span-2 order-2 lg:order-1 space-y-6">
                    <!-- Order Items -->
                    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                            <h2 class="text-lg font-bold text-gray-900">
                                @if ($tipo_pedido === 'servicio')
                                    Servicios
                                @elseif($tipo_pedido === 'mixto')
                                    Productos y Servicios
                                @else
                                    Productos
                                @endif
                            </h2>
                            <span class="bg-purple-100 text-purple-700 py-1 px-3 rounded-full text-xs font-bold">
                                {{ count($items) }} Items
                            </span>
                        </div>

                        <div class="divide-y divide-gray-100">
                            @foreach ($items as $item)
                                <div class="p-4 hover:bg-gray-50 transition-colors duration-200">
                                    <div class="flex space-x-4">
                                        <!-- Item Image -->
                                        <div class="flex-shrink-0">
                                            <div
                                                class="w-20 h-20 rounded-lg overflow-hidden border border-gray-200 shadow-sm flex items-center justify-center bg-purple-50">
                                                @if ($item['image'])
                                                    <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}"
                                                        class="w-full h-full object-cover">
                                                @else
                                                    <span
                                                        class="text-2xl font-bold text-purple-200 uppercase select-none">
                                                        {{ substr($item['name'], 0, 1) }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>

                                        <!-- Item Details -->
                                        <div class="flex-1 min-w-0 flex flex-col justify-between">
                                            <div>
                                                <div class="flex justify-between items-start">
                                                    <h3 class="font-bold text-gray-900 text-base">
                                                        {{ $item['name'] }}
                                                    </h3>
                                                    <p class="font-bold text-purple-600 text-lg">
                                                        ${{ number_format($item['price'] * $item['quantity'], 2) }}
                                                    </p>
                                                </div>

                                                @if (isset($item['variant']) && $item['variant'])
                                                    <p class="text-sm text-gray-500 mt-1">{{ $item['variant'] }}</p>
                                                @endif

                                                <!-- Service specific info -->
                                                @if ($item['type'] === 'servicio')
                                                    <div class="mt-2 flex flex-wrap gap-2">
                                                        @if (isset($item['fecha_reserva']) && isset($item['hora_reserva']))
                                                            <span
                                                                class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-blue-100 text-blue-800">
                                                                <svg class="mr-1.5 h-3 w-3" fill="none"
                                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round"
                                                                        stroke-linejoin="round" stroke-width="2"
                                                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                                </svg>
                                                                {{ \Carbon\Carbon::parse($item['fecha_reserva'])->format('d/m/Y') }}
                                                                - {{ $item['hora_reserva'] }}
                                                            </span>
                                                        @endif
                                                        @if (isset($item['empleado_nombre']))
                                                            <span
                                                                class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-green-100 text-green-800">
                                                                <svg class="mr-1.5 h-3 w-3" fill="none"
                                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round"
                                                                        stroke-linejoin="round" stroke-width="2"
                                                                        d="M5.121 17.804A13.937 13.937 0 0112 15c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0z" />
                                                                </svg>
                                                                {{ $item['empleado_nombre'] }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>

                                            <div class="flex items-center text-sm text-gray-500 mt-2">
                                                <span>Cantidad: <span class="font-medium text-gray-900">
                                                        @if (isset($item['cantidad_decimal']) && $item['cantidad_decimal'])
                                                            {{ $item['cantidad_decimal'] }}
                                                            {{ $item['unidad_medida'] ?? 'kg' }}
                                                        @else
                                                            {{ $item['quantity'] }}
                                                        @endif
                                                    </span></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </main>
</div>
