<div>
    <!-- Header -->
    <header class="sticky top-0 z-10 bg-white shadow-sm">
        <div class="container mx-auto px-4 py-3 flex items-center justify-between">
            <!-- Back Button -->
            <button wire:click="closeModal" class="p-2 -ml-2 rounded-full hover:bg-gray-100">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-600" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </button>

            <!-- Title -->
            <h1 class="text-lg font-semibold text-gray-900">
                @if ($tipo_pedido === 'servicio')
                    Detalle de la Reserva
                @else
                    Detalle del Pedido
                @endif
            </h1>

            <!-- Close Button -->
            <button wire:click="closeModal" class="p-2 rounded-full hover:bg-gray-100">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-600" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </header>

    <main class="pb-24 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Column: Order Items (Takes up 2/3 on desktop) -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Order Items -->
                    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                            <h2 class="text-lg font-bold text-gray-900">
                                @if ($tipo_pedido === 'servicio')
                                    Servicios Solicitados
                                @elseif($tipo_pedido === 'mixto')
                                    Productos y Servicios
                                @else
                                    Productos Solicitados
                                @endif
                            </h2>
                            <span class="bg-purple-100 text-purple-700 py-1 px-3 rounded-full text-xs font-bold">
                                {{ count($detalles) }} Items
                            </span>
                        </div>

                        <div class="divide-y divide-gray-100">
                            @foreach ($detalles as $detalle)
                                <div class="p-4 hover:bg-gray-50 transition-colors duration-200">
                                    <div class="flex space-x-4">
                                        <!-- Item Image -->
                                        <div class="flex-shrink-0">
                                            <div
                                                class="w-20 h-20 rounded-lg overflow-hidden border border-gray-200 shadow-sm flex items-center justify-center bg-purple-50">
                                                @if ($detalle->producto->imagen_url)
                                                    <img src="{{ $detalle->producto->imagen_url }}"
                                                        alt="{{ $detalle->producto->nombre ?? 'Producto' }}"
                                                        class="w-full h-full object-cover">
                                                @else
                                                    <span
                                                        class="text-2xl font-bold text-purple-200 uppercase select-none">
                                                        {{ substr($detalle->producto->nombre ?? 'P', 0, 1) }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>

                                        <!-- Item Details -->
                                        <div class="flex-1 min-w-0 flex flex-col justify-between">
                                            <div>
                                                <div class="flex justify-between items-start">
                                                    <h3 class="font-bold text-gray-900 text-base">
                                                        {{ $detalle->producto->nombre ?? 'Producto' }}
                                                    </h3>
                                                    <p class="font-bold text-purple-600 text-lg">
                                                        ${{ number_format($detalle->subtotal, 2) }}
                                                    </p>
                                                </div>

                                                @if ($detalle->variantesArticulos)
                                                    <p class="text-sm text-gray-500 mt-1">
                                                        {{ $detalle->variantesArticulos->descripcion_variante }}
                                                    </p>
                                                @endif

                                                <!-- Service specific info -->
                                                @if ($detalle->idservicio)
                                                    <div class="mt-2 flex flex-wrap gap-2">
                                                        @if ($detalle->fecha_reserva && $detalle->hora_reserva)
                                                            <span
                                                                class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-blue-100 text-blue-800">
                                                                <svg class="mr-1.5 h-3 w-3" fill="none"
                                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="2"
                                                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                                </svg>
                                                                {{ \Carbon\Carbon::parse($detalle->fecha_reserva)->format('d/m/Y') }}
                                                                - {{ $detalle->hora_reserva }}
                                                            </span>
                                                        @endif
                                                        @if ($detalle->empleado)
                                                            <span
                                                                class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-green-100 text-green-800">
                                                                <svg class="mr-1.5 h-3 w-3" fill="none"
                                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="2"
                                                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                                </svg>
                                                                {{ $detalle->empleado->nombre }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>

                                            <div class="flex items-center text-sm text-gray-500 mt-2">
                                                <span>Cantidad: <span class="font-medium text-gray-900">
                                                        @if ($detalle->cantidad_decimal)
                                                            {{ $detalle->cantidad_decimal }}
                                                            {{ $detalle->unidad_medida_pedido ?? 'kg' }}
                                                        @else
                                                            {{ $detalle->cantidad }}
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

                <!-- Right Column: Info & Summary (Takes up 1/3 on desktop) -->
                <div class="lg:col-span-1 space-y-6">

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

                    <!-- Customer Info Card -->
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

                    <!-- Delivery & Payment Info Card -->
                    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100">
                            <h2 class="text-lg font-bold text-gray-900">Detalles de Entrega</h2>
                        </div>
                        <div class="p-6 space-y-4">
                            @if ($tipo_pedido === 'producto' || $tipo_pedido === 'mixto')
                                <div class="flex items-start space-x-3">
                                    <div class="bg-blue-50 p-2 rounded-lg">
                                        <svg class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Dirección</p>
                                        <p class="font-medium text-gray-900">{{ $direccion }}
                                        </p>
                                        <p class="text-xs text-gray-500">{{ $ciudad }},
                                            {{ $codigo_postal }}</p>
                                        @if ($notas_entrega)
                                            <p class="text-xs text-gray-500 mt-1 italic">
                                                "{{ $notas_entrega }}"</p>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            <div class="flex items-start space-x-3">
                                <div class="bg-green-50 p-2 rounded-lg">
                                    <svg class="h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Método de Pago</p>
                                    <p class="font-medium text-gray-900">
                                        {{ $metodo_pago === 'efectivo' ? 'Efectivo' : 'Tarjeta' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </main>
</div>
```
