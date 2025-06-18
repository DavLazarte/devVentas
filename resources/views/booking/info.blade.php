 <!-- Sample Data -->
 @php
 $booking = [
     'service_name' => 'Corte y Peinado Premium',
     'service_image' => asset('images/haircut-service.jpg'),
     'shop_name' => 'Salón Elegance',
     'price' => 45.99,
     'date' => 'Viernes 15 de Enero',
     'time' => '10:30 AM',
     'duration' => '60 minutos',
 ];
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Información de Reserva - Tienda Dux</title>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

<style>
        .hide-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .hide-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
</head>

<body class="bg-gray-50">
    <!-- Header -->
    <header class="sticky top-0 z-10 bg-white shadow-sm">
        <div class="container mx-auto px-4 py-3 flex items-center justify-between">
            <!-- Back Button -->
            <button onclick="history.back()" class="p-2 -ml-2 rounded-full hover:bg-gray-100">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-600" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </button>

            <!-- Title -->
            <h1 class="text-lg font-semibold text-gray-900">Información Personal</h1>

            <!-- Progress -->
            <div class="text-sm text-gray-600">3/3</div>
        </div>
    </header>

    <main class="pb-24">
        <!-- Booking Summary -->
        <div class="bg-white px-4 py-4 mb-2">
            <h2 class="text-lg font-semibold text-gray-900 mb-3">Resumen de tu reserva</h2>
            <div class="space-y-3">
                <!-- Service -->
                <div class="flex items-center space-x-3">
                    <div class="w-12 h-12 rounded-lg overflow-hidden border border-gray-200">
                        <img src="{{ $booking['service_image'] }}" alt="{{ $booking['service_name'] }}"
                            class="w-full h-full object-cover">
                    </div>
                    <div class="flex-1">
                        <h3 class="font-semibold text-gray-900">{{ $booking['service_name'] }}</h3>
                        <p class="text-sm text-gray-600">{{ $booking['shop_name'] }}</p>
                    </div>
                    <div class="text-right">
                        <p class="font-semibold text-purple-600">${{ number_format($booking['price'], 2) }}</p>
                    </div>
                </div>

                <!-- Date & Time -->
                <div class="bg-gray-50 rounded-lg p-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span class="font-medium text-gray-900">{{ $booking['date'] }}</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="font-medium text-gray-900">{{ $booking['time'] }}</span>
                        </div>
                    </div>
                    <div class="mt-2 text-sm text-gray-600">
                        Duración: {{ $booking['duration'] }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Customer Information Form -->
        <div class="bg-white px-4 py-4 mb-2">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Tus datos</h2>

            <form class="space-y-4">
                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nombre completo *</label>
                    <input type="text" id="name" name="name" placeholder="Ingresa tu nombre completo"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-purple-500 focus:border-purple-500"
                        required>
                </div>

                <!-- Phone -->
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Teléfono *</label>
                    <input type="tel" id="phone" name="phone" placeholder="+54 11 1234-5678"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-purple-500 focus:border-purple-500"
                        required>
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                    <input type="email" id="email" name="email" placeholder="tu@email.com"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-purple-500 focus:border-purple-500"
                        required>
                </div>

                <!-- Comments -->
                <div>
                    <label for="comments" class="block text-sm font-medium text-gray-700 mb-1">Comentarios adicionales
                        (opcional)</label>
                    <textarea id="comments" name="comments" rows="3"
                        placeholder="¿Alguna preferencia especial o comentario para el profesional?"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-purple-500 focus:border-purple-500 resize-none"></textarea>
                </div>
            </form>
        </div>

        <!-- Account Creation -->
        <div class="bg-white px-4 py-4 mb-2">
            <div class="flex items-start space-x-3">
                <input type="checkbox" id="create_account" name="create_account"
                    class="mt-1 text-purple-600 focus:ring-purple-500 rounded">
                <div>
                    <label for="create_account" class="font-medium text-gray-900 cursor-pointer">
                        Crear una cuenta
                    </label>
                    <p class="text-sm text-gray-600 mt-1">
                        Guarda tu información para futuras reservas y recibe ofertas exclusivas
                    </p>
                </div>
            </div>
        </div>

        <!-- Cancellation Policy -->
        <div class="bg-white px-4 py-4 mb-2">
            <h2 class="text-lg font-semibold text-gray-900 mb-3">Política de cancelación</h2>
            <div class="space-y-2 text-sm text-gray-700">
                <p>• Puedes cancelar tu reserva hasta 2 horas antes sin costo</p>
                <p>• Cancelaciones con menos de 2 horas pueden tener un cargo del 50%</p>
                <p>• No presentarse sin cancelar tendrá un cargo del 100%</p>
            </div>
        </div>

        <!-- Terms -->
        <div class="bg-white px-4 py-4">
            <div class="flex items-start space-x-3">
                <input type="checkbox" id="terms" name="terms"
                    class="mt-1 text-purple-600 focus:ring-purple-500 rounded" required>
                <div>
                    <label for="terms" class="text-sm text-gray-700 cursor-pointer">
                        Acepto los <a href="#" class="text-purple-600 underline">términos y condiciones</a> y la
                        <a href="#" class="text-purple-600 underline">política de cancelación</a>
                    </label>
                </div>
            </div>
        </div>
    </main>

    <!-- Fixed Bottom Confirm Button -->
    <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 p-4 z-10">
        <div class="flex items-center justify-between mb-3">
            <span class="text-sm text-gray-600">Total a pagar:</span>
            <span class="text-xl font-bold text-purple-600">${{ number_format($booking['price'], 2) }}</span>
        </div>
        <a href="{{ url('booking/confirmation') }}"
            class="block w-full bg-purple-600 text-white py-3 px-4 rounded-lg font-medium text-lg text-center hover:bg-purple-700 transition-colors">
            Confirmar reserva
        </a>
    </div>

   

    <script src="{{ asset('js/app.js') }}"></script>
</body>

</html>
