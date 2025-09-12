<div>
    <!-- Header -->
    <header class="sticky top-0 z-10 bg-white shadow-sm">
        <div class="container mx-auto px-4 py-3 flex items-center justify-between">
            <!-- Back Button -->
            <button onclick="history.back()" class="p-2 -ml-2 rounded-full hover:bg-gray-100">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </button>

            <!-- Title -->
            @php
                $tieneServicios = collect($items)->contains('type', 'servicio');
                $tieneProductos = collect($items)->contains('type', 'articulo');
            @endphp
            <h1 class="text-lg font-semibold text-gray-900">
                @if($tieneServicios && $tieneProductos)
                    Resumen de Pedido
                @elseif($tieneServicios)
                    Reserva de Servicio
                @else
                    Resumen de Compra
                @endif
            </h1>

            <!-- Cart Icon with Count -->
            <div class="relative">
                @if($tieneServicios)
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                @else
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                @endif
                <span class="absolute -top-1 -right-1 bg-purple-600 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                    {{ count($items) }}
                </span>
            </div>
        </div>
    </header>

    <main class="pb-24">
        <!-- Cart Items -->
        <div class="bg-white mb-2">
            <div class="px-4 py-3 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">
                    @if($tieneServicios && $tieneProductos)
                        Tu pedido
                    @elseif($tieneServicios)
                        Tu reserva
                    @else
                        Tu pedido
                    @endif
                </h2>
                <p class="text-sm text-gray-600">{{ count($items) }} {{ count($items) === 1 ? 'elemento' : 'elementos' }}</p>
            </div>

            <div class="divide-y divide-gray-200">
                @foreach ($items as $item)
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
                                        <p class="text-xs text-gray-500 mt-0.5">{{ $item['shop'] }}</p>
                                        @if (isset($item['variant']) && $item['variant'])
                                            <p class="text-xs text-gray-500">{{ $item['variant'] }}</p>
                                        @endif

                                        <!-- Service Reservation Info -->
                                        @if($item['type'] === 'servicio')
                                            @if(isset($item['fecha_servicio']) && isset($item['hora_inicio']))
                                                <div class="mt-2 p-2 bg-purple-50 rounded-md">
                                                    <div class="flex items-center space-x-1 text-purple-700">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                        </svg>
                                                        <span class="text-xs font-medium">Fecha: {{ \Carbon\Carbon::parse($item['fecha_servicio'])->format('d/m/Y') }}</span>
                                                    </div>
                                                    <div class="flex items-center space-x-1 text-purple-700 mt-1">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>
                                                        <span class="text-xs font-medium">Hora: {{ $item['hora_inicio'] }}</span>
                                                    </div>
                                                    @if(isset($item['duracion']))
                                                        <div class="flex items-center space-x-1 text-purple-700 mt-1">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                                            </svg>
                                                            <span class="text-xs font-medium">Duración: {{ $item['duracion'] }} min</span>
                                                        </div>
                                                    @endif
                                                    @if(isset($item['empleado_nombre']))
                                                        <div class="flex items-center space-x-1 text-purple-700 mt-1">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 15c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0z" />
                                                            </svg>
                                                            <span class="text-xs font-medium">Profesional: {{ $item['empleado_nombre'] }}</span>
                                                        </div>
                                                    @endif
                                                </div>
                                            @else
                                                <div class="mt-2 p-2 bg-blue-50 rounded-md">
                                                    <span class="text-xs text-blue-700 font-medium">Servicio sin turno fijo</span>
                                                </div>
                                            @endif
                                        @endif
                                    </div>
                                </div>

                                <!-- Quantity and Price -->
                                <div class="flex items-center justify-between mt-2">
                                    <span class="text-sm text-gray-500">Cantidad: {{ $item['quantity'] }}</span>
                                    <div class="text-right">
                                        <p class="font-semibold text-purple-600">
                                            ${{ number_format($item['price'] * $item['quantity'], 2) }}</p>
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
                @if($tieneProductos)
                    <div class="flex justify-between">
                        <span class="text-gray-600">Envío</span>
                        <span class="font-medium">${{ number_format($envio, 2) }}</span>
                    </div>
                @endif
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

        <!-- Customer Information -->
        <div class="bg-white px-4 py-4 mb-2">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Información de contacto</h2>

            <form wire:submit.prevent="procesarPedido" class="space-y-4">
                <!-- Name -->
                <div>
                    <label for="nombre_cliente" class="block text-sm font-medium text-gray-700 mb-1">Nombre completo</label>
                    <input type="text" id="nombre_cliente" wire:model.defer="nombre_cliente" placeholder="Ingresa tu nombre completo"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-purple-500 focus:border-purple-500">
                    @error('nombre_cliente') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <!-- Phone -->
                <div>
                    <label for="telefono" class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                    <input type="tel" id="telefono" wire:model.defer="telefono" placeholder="+54 11 1234-5678"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-purple-500 focus:border-purple-500">
                    @error('telefono') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" id="email" wire:model.defer="email" placeholder="tu@email.com"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-purple-500 focus:border-purple-500">
                    @error('email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <!-- Address - Solo mostrar para productos o servicios que requieran entrega -->
                @if($tieneProductos)
                    <!-- Address -->
                    <div>
                        <label for="direccion" class="block text-sm font-medium text-gray-700 mb-1">Dirección de entrega</label>
                        <input type="text" id="direccion" wire:model.defer="direccion" placeholder="Calle, número, piso, departamento"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-purple-500 focus:border-purple-500">
                        @error('direccion') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- City and Postal Code -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="ciudad" class="block text-sm font-medium text-gray-700 mb-1">Ciudad</label>
                            <input type="text" id="ciudad" wire:model.defer="ciudad" placeholder="Buenos Aires"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-purple-500 focus:border-purple-500">
                            @error('ciudad') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="codigo_postal" class="block text-sm font-medium text-gray-700 mb-1">Código Postal</label>
                            <input type="text" id="codigo_postal" wire:model.defer="codigo_postal" placeholder="1234"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-purple-500 focus:border-purple-500">
                            @error('codigo_postal') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Delivery Notes -->
                    <div>
                        <label for="notas_entrega" class="block text-sm font-medium text-gray-700 mb-1">Notas de entrega (opcional)</label>
                        <textarea id="notas_entrega" wire:model.defer="notas_entrega" rows="3" placeholder="Instrucciones especiales para la entrega..."
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-purple-500 focus:border-purple-500 resize-none"></textarea>
                    </div>
                @else
                    <!-- Para servicios, notas especiales -->
                    <div>
                        <label for="notas_entrega" class="block text-sm font-medium text-gray-700 mb-1">Notas adicionales (opcional)</label>
                        <textarea id="notas_entrega" wire:model.defer="notas_entrega" rows="3" placeholder="Comentarios adicionales sobre el servicio..."
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-purple-500 focus:border-purple-500 resize-none"></textarea>
                    </div>
                @endif

                <!-- Payment Method -->
                <div class="space-y-3">
                    <h3 class="font-medium text-gray-900">Método de pago</h3>

                    <!-- Cash on Delivery -->
                    <label class="flex items-center space-x-3 p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50">
                        <input type="radio" wire:model="metodo_pago" value="efectivo" class="text-purple-600 focus:ring-purple-500">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">Efectivo/Transferencia</p>
                                <p class="text-sm text-gray-600">
                                    @if($tieneServicios && !$tieneProductos)
                                        Paga cuando recibas el servicio
                                    @else
                                        Paga cuando recibas tu pedido
                                    @endif
                                </p>
                            </div>
                        </div>
                    </label>

                    <!-- Credit Card -->
                    <label class="flex items-center space-x-3 p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50">
                        <input type="radio" wire:model="metodo_pago" value="tarjeta" class="text-purple-600 focus:ring-purple-500">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">Tarjeta de crédito/débito</p>
                                <p class="text-sm text-gray-600">Puede contener incremento el precio del producto/servicio</p>
                            </div>
                        </div>
                    </label>
                </div>

                <!-- Terms and Conditions -->
                <div class="flex items-start space-x-3">
                    <input type="checkbox" id="terms" wire:model="acepto_terminos" required class="mt-1 text-purple-600 focus:ring-purple-500 rounded">
                    <div>
                        <label for="terms" class="text-sm text-gray-700 cursor-pointer">
                            Acepto los <a href="{{url('/terminos')}}" class="text-purple-600 underline">términos y condiciones</a> y la
                            <a href="{{url('/politicas')}}" class="text-purple-600 underline">política de privacidad</a>
                        </label>
                        @error('acepto_terminos') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="w-full bg-purple-600 text-white py-3 px-4 rounded-lg font-medium text-lg hover:bg-purple-700 transition-colors">
                    @if($tieneServicios && !$tieneProductos)
                        Confirmar reserva
                    @elseif($tieneServicios && $tieneProductos)
                        Confirmar pedido y reserva
                    @else
                        Confirmar pedido
                    @endif
                </button>
            </form>
        </div>
    </main>
</div>
