<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedido Confirmado - Tienda Dux</title>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>

<body class="bg-gray-50">
    <!-- Header -->
    <header class="sticky top-0 z-10 bg-white shadow-sm">
        <div class="container mx-auto px-4 py-3 flex items-center justify-center">
            <h1 class="text-lg font-semibold text-gray-900">Pedido Confirmado</h1>
        </div>
    </header>

    <main class="pb-6">
        <!-- Success Message -->
        <div class="bg-white px-4 py-8 mb-2 text-center">
            <!-- Success Icon -->
            <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-green-600" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>

            <h1 class="text-2xl font-bold text-gray-900 mb-2">¡Pedido confirmado!</h1>
            <p class="text-gray-600 mb-4">Tu pedido ha sido procesado exitosamente</p>

            <!-- Order Reference -->
            <div class="bg-gray-50 rounded-lg p-4 mb-4">
                <p class="text-sm text-gray-600 mb-1">Número de pedido</p>
                <p class="text-lg font-bold text-purple-600">#{{ $pedido->id }}</p>
            </div>

            <p class="text-sm text-gray-600">
                Hemos enviado los detalles de tu pedido a <span class="font-medium">{{ $pedido->email }}</span>
            </p>
        </div>

        <!-- Order Details -->
        <div class="bg-white px-4 py-4 mb-2">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Detalles de tu pedido</h2>

            @foreach ($pedido->detalles as $detalle)
                <div class="flex items-center space-x-3 mb-4">
                    {{-- <div class="w-16 h-16 rounded-lg overflow-hidden border border-gray-200">
                    <img src="{{ $detalle->producto->imagen ?? asset('images/placeholder.jpg') }}"
                         alt="{{ $detalle->producto->nombre ?? 'Producto' }}"
                         class="w-full h-full object-cover">
                </div> --}}
                    <div class="flex-1">
                        <h3 class="font-semibold text-gray-900">{{ $detalle->producto->nombre ?? 'Producto' }}</h3>
                        <p class="text-sm text-gray-600">{{ $detalle->producto->local->nombre ?? 'Tienda' }}</p>
                        @if ($detalle->empleado)
                            <p class="text-sm text-gray-700 mt-1">Profesional: <span class="font-medium">{{ $detalle->empleado->nombre }}</span></p>
                        @endif
                        @if ($detalle->variante)
                            <p class="text-sm text-gray-600">{{ $detalle->variante }}</p>
                        @endif
                    </div>
                    <div class="text-right">
                        <p class="font-semibold text-purple-600">${{ number_format($detalle->subtotal, 2) }}</p>
                        <p class="text-sm text-gray-600">{{ $detalle->cantidad }} x
                            ${{ number_format($detalle->precio_unitario, 2) }}</p>
                    </div>
                </div>
            @endforeach

            <!-- Order Summary -->
            <div class="border-t border-gray-200 pt-4 mt-4">
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Subtotal</span>
                        <span class="font-medium">${{ number_format($pedido->subtotal, 2) }}</span>
                    </div>
                    @if ($pedido->envio > 0)
                        <div class="flex justify-between">
                            <span class="text-gray-600">Envío</span>
                            <span class="font-medium">${{ number_format($pedido->envio, 2) }}</span>
                        </div>
                    @endif
                    @if ($pedido->descuento > 0)
                        <div class="flex justify-between">
                            <span class="text-gray-600">Descuento</span>
                            <span class="font-medium">-${{ number_format($pedido->descuento, 2) }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between pt-2 border-t border-gray-200">
                        <span class="font-semibold text-gray-900">Total</span>
                        <span class="font-bold text-purple-600">${{ number_format($pedido->total, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customer Info -->
        <div class="bg-white px-4 py-4 mb-2">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Información del cliente</h2>
            <div class="space-y-2">
                <div class="flex justify-between">
                    <span class="text-gray-600">Nombre</span>
                    <span class="font-medium">{{ $pedido->nombre_cliente }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Email</span>
                    <span class="font-medium">{{ $pedido->email }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Teléfono</span>
                    <span class="font-medium">{{ $pedido->telefono }}</span>
                </div>

                <!-- Solo mostrar información de dirección si NO es solo servicio -->
                @if ($pedido->tipo_pedido !== 'servicio')
                    <div class="flex justify-between">
                        <span class="text-gray-600">Dirección</span>
                        <span class="font-medium">{{ $pedido->direccion }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Ciudad</span>
                        <span class="font-medium">{{ $pedido->ciudad }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Código Postal</span>
                        <span class="font-medium">{{ $pedido->codigo_postal }}</span>
                    </div>
                @endif

                @if ($pedido->notas_entrega)
                    <div class="flex justify-between">
                        <span class="text-gray-600">
                            @if ($pedido->tipo_pedido === 'servicio')
                                Notas
                            @else
                                Notas de entrega
                            @endif
                        </span>
                        <span class="font-medium">{{ $pedido->notas_entrega }}</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- Payment Method -->
        <div class="bg-white px-4 py-4 mb-2">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Método de pago</h2>
            <div class="flex items-center space-x-3">
                <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-purple-600" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <div>
                    <p class="font-medium text-gray-900">{{ ucfirst($pedido->metodo_pago) }}</p>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="space-y-3 px-4">
            <!-- Back to Home -->
            <a href="{{ url('/') }}"
                class="block w-full bg-purple-600 text-white py-3 px-4 rounded-lg font-medium text-center hover:bg-purple-700 transition-colors">
                Volver al inicio
            </a>

            <!-- My Orders activar cuando este el perfil activado -->
            {{-- <a href="{{ url('/mis-pedidos') }}" class="block w-full bg-white border border-purple-600 text-purple-600 py-3 px-4 rounded-lg font-medium text-center hover:bg-purple-50 transition-colors">
                Ver mis pedidos
            </a> --}}
        </div>
    </main>

    <script src="{{ asset('js/app.js') }}"></script>
</body>

</html>
