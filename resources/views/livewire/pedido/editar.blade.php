<div>
    <!-- Header -->
    <header class="sticky top-0 z-10 bg-white shadow-sm">
        <div class="container mx-auto px-4 py-3 flex items-center justify-between">
            <!-- Back Button -->
            <button wire:click="closeModal" class="p-2 -ml-2 rounded-full hover:bg-gray-100">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </button>

            <!-- Title -->
            <h1 class="text-lg font-semibold text-gray-900">Editar Estado del Pedido</h1>

            <!-- Close Button -->
            <button wire:click="closeModal" class="p-2 rounded-full hover:bg-gray-100">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </header>

    <main class="pb-24">
        <!-- Order Details -->
        <div class="bg-white mb-2">
            <div class="px-4 py-3 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Información del Pedido</h2>
            </div>

            <div class="px-4 py-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Customer Info -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h3 class="font-medium text-gray-900 mb-3 flex items-center">
                            <svg class="h-5 w-5 text-gray-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Información del Cliente
                        </h3>
                        <div class="space-y-2">
                            <div class="flex items-center">
                                <svg class="h-5 w-5 text-gray-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                <p class="text-sm"><span class="font-medium">Nombre:</span> {{ $nombre_cliente }}</p>
                            </div>
                            <div class="flex items-center">
                                <svg class="h-5 w-5 text-gray-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                <p class="text-sm"><span class="font-medium">Email:</span> {{ $email }}</p>
                            </div>
                            <div class="flex items-center">
                                <svg class="h-5 w-5 text-gray-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                                <p class="text-sm"><span class="font-medium">Teléfono:</span> {{ $telefono }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Delivery Info -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h3 class="font-medium text-gray-900 mb-3 flex items-center">
                            <svg class="h-5 w-5 text-gray-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Información de Entrega
                        </h3>
                        <div class="space-y-2">
                            <div class="flex items-start">
                                <svg class="h-5 w-5 text-gray-400 mr-2 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <p class="text-sm"><span class="font-medium">Dirección:</span> {{ $direccion }}</p>
                            </div>
                            <div class="flex items-center">
                                <svg class="h-5 w-5 text-gray-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                </svg>
                                <p class="text-sm"><span class="font-medium">Ciudad:</span> {{ $ciudad }}</p>
                            </div>
                            <div class="flex items-center">
                                <svg class="h-5 w-5 text-gray-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                <p class="text-sm"><span class="font-medium">Código Postal:</span> {{ $codigo_postal }}</p>
                            </div>
                            @if($notas_entrega)
                                <div class="flex items-start">
                                    <svg class="h-5 w-5 text-gray-400 mr-2 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                                    </svg>
                                    <p class="text-sm"><span class="font-medium">Notas:</span> {{ $notas_entrega }}</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Payment Info -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h3 class="font-medium text-gray-900 mb-3 flex items-center">
                            <svg class="h-5 w-5 text-gray-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                            Información de Pago
                        </h3>
                        <div class="space-y-2">
                            <div class="flex items-center">
                                <svg class="h-5 w-5 text-gray-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                </svg>
                                <p class="text-sm">
                                    <span class="font-medium">Método de Pago:</span>
                                    {{ $metodo_pago === 'efectivo' ? 'Efectivo contra entrega' : 'Tarjeta de crédito/débito' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Items -->
        <div class="bg-white mb-2">
            <div class="px-4 py-3 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Productos</h2>
            </div>

            <div class="divide-y divide-gray-200">
                @foreach($items as $item)
                    <div class="px-4 py-4">
                        <div class="flex space-x-3">
                            <!-- Item Image -->
                            <div class="flex-shrink-0">
                                <div class="w-16 h-16 rounded-lg overflow-hidden border border-gray-200">
                                    <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" class="w-full h-full object-cover">
                                </div>
                            </div>

                            <!-- Item Details -->
                            <div class="flex-1 min-w-0">
                                <div class="flex justify-between">
                                    <div class="flex-1">
                                        <h3 class="font-medium text-gray-900 text-sm">{{ $item['name'] }}</h3>
                                        @if(isset($item['variant']) && $item['variant'])
                                            <p class="text-xs text-gray-500">{{ $item['variant'] }}</p>
                                        @endif
                                    </div>
                                </div>

                                <!-- Quantity and Price -->
                                <div class="flex items-center justify-between mt-2">
                                    <span class="text-sm text-gray-500">Cantidad: {{ $item['quantity'] }}</span>
                                    <div class="text-right">
                                        <p class="font-semibold text-purple-600">
                                            ${{ number_format($item['price'] * $item['quantity'], 2) }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Order Summary -->
        <div class="bg-white px-4 py-4 mb-2">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Resumen del pedido</h2>
            <div class="space-y-2">
                <div class="flex justify-between">
                    <span class="text-gray-600">Subtotal</span>
                    <span class="font-medium">${{ number_format($subtotal, 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Envío</span>
                    <span class="font-medium">${{ number_format($envio, 2) }}</span>
                </div>
                @if($descuento > 0)
                    <div class="flex justify-between text-green-600">
                        <span>Descuento</span>
                        <span class="font-medium">-${{ number_format($descuento, 2) }}</span>
                    </div>
                @endif
                <div class="border-t border-gray-200 pt-2 mt-2">
                    <div class="flex justify-between">
                        <span class="font-semibold">Total</span>
                        <span class="font-semibold text-purple-600">${{ number_format($total, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estado del Pedido -->
        <div class="bg-white px-4 py-4 mb-2">
            <form wire:submit.prevent="guardar" class="space-y-4">
                <div>
                    <label for="estado" class="block text-sm font-medium text-gray-700 mb-1">Estado del Pedido</label>
                    <select id="estado" wire:model="estado" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-purple-500 focus:border-purple-500">
                        <option value="pendiente">Pendiente</option>
                        <option value="en_proceso">En Proceso</option>
                        <option value="enviado">Enviado</option>
                        <option value="entregado">Entregado</option>
                        <option value="cancelado">Cancelado</option>
                    </select>
                    @error('estado') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                <button type="submit" class="w-full bg-purple-600 text-white py-3 px-4 rounded-lg font-medium text-lg hover:bg-purple-700 transition-colors">
                    Guardar cambios
                </button>
            </form>
        </div>
    </main>
</div>
