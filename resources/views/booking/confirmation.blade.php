 <!-- Sample Data -->
 @php
     $booking = [
         'reference' => 'BK2024001234',
         'service_name' => 'Corte y Peinado Premium',
         'service_image' => asset('images/haircut-service.jpg'),
         'shop_name' => 'Salón Elegance',
         'shop_logo' => asset('images/salon-logo.jpg'),
         'shop_rating' => 4.8,
         'shop_address' => 'Av. Libertador 1234, Buenos Aires',
         'shop_phone' => '+54 11 1234-5678',
         'staff_name' => 'María González',
         'price' => 45.99,
         'date' => 'Viernes 15 de Enero, 2024',
         'time' => '10:30 AM',
         'duration' => '60 minutos',
         'customer_name' => 'Juan Pérez',
         'customer_phone' => '+54 11 9876-5432',
         'customer_email' => 'juan.perez@email.com',
     ];
 @endphp

 <!DOCTYPE html>
 <html lang="en">

 <head>
     <meta charset="UTF-8">
     <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <title>Reserva Confirmada - Tienda Dux</title>
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
         <div class="container mx-auto px-4 py-3 flex items-center justify-center">
             <h1 class="text-lg font-semibold text-gray-900">Reserva Confirmada</h1>
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

             <h1 class="text-2xl font-bold text-gray-900 mb-2">¡Reserva confirmada!</h1>
             <p class="text-gray-600 mb-4">Tu cita ha sido programada exitosamente</p>

             <!-- Booking Reference -->
             <div class="bg-gray-50 rounded-lg p-4 mb-4">
                 <p class="text-sm text-gray-600 mb-1">Número de reserva</p>
                 <p class="text-lg font-bold text-purple-600">#{{ $booking['reference'] }}</p>
             </div>

             <p class="text-sm text-gray-600">
                 Hemos enviado los detalles de tu reserva a <span
                     class="font-medium">{{ $booking['customer_email'] }}</span>
             </p>
         </div>

         <!-- Booking Details -->
         <div class="bg-white px-4 py-4 mb-2">
             <h2 class="text-lg font-semibold text-gray-900 mb-4">Detalles de tu reserva</h2>

             <!-- Service Info -->
             <div class="flex items-center space-x-3 mb-4">
                 <div class="w-16 h-16 rounded-lg overflow-hidden border border-gray-200">
                     <img src="{{ $booking['service_image'] }}" alt="{{ $booking['service_name'] }}"
                         class="w-full h-full object-cover">
                 </div>
                 <div class="flex-1">
                     <h3 class="font-semibold text-gray-900">{{ $booking['service_name'] }}</h3>
                     <p class="text-sm text-gray-600">{{ $booking['shop_name'] }}</p>
                     <p class="text-sm text-gray-600">con {{ $booking['staff_name'] }}</p>
                 </div>
                 <div class="text-right">
                     <p class="font-semibold text-purple-600">${{ number_format($booking['price'], 2) }}</p>
                 </div>
             </div>

             <!-- Date & Time -->
             <div class="grid grid-cols-2 gap-4 mb-4">
                 <div class="bg-gray-50 rounded-lg p-3">
                     <div class="flex items-center space-x-2 mb-1">
                         <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor">
                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                 d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                         </svg>
                         <span class="text-xs text-gray-600 font-medium">FECHA</span>
                     </div>
                     <p class="font-semibold text-gray-900">{{ $booking['date'] }}</p>
                 </div>

                 <div class="bg-gray-50 rounded-lg p-3">
                     <div class="flex items-center space-x-2 mb-1">
                         <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor">
                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                 d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                         </svg>
                         <span class="text-xs text-gray-600 font-medium">HORA</span>
                     </div>
                     <p class="font-semibold text-gray-900">{{ $booking['time'] }}</p>
                     <p class="text-xs text-gray-600">{{ $booking['duration'] }}</p>
                 </div>
             </div>

             <!-- Customer Info -->
             <div class="border-t border-gray-200 pt-4">
                 <h3 class="font-medium text-gray-900 mb-2">Información del cliente</h3>
                 <div class="space-y-1 text-sm">
                     <p><span class="text-gray-600">Nombre:</span> <span
                             class="font-medium">{{ $booking['customer_name'] }}</span></p>
                     <p><span class="text-gray-600">Teléfono:</span> <span
                             class="font-medium">{{ $booking['customer_phone'] }}</span></p>
                     <p><span class="text-gray-600">Email:</span> <span
                             class="font-medium">{{ $booking['customer_email'] }}</span></p>
                 </div>
             </div>
         </div>

         <!-- Shop Information -->
         <div class="bg-white px-4 py-4 mb-2">
             <h2 class="text-lg font-semibold text-gray-900 mb-3">Información del establecimiento</h2>
             <div class="flex items-center space-x-3 mb-3">
                 <div class="w-12 h-12 rounded-full overflow-hidden border border-gray-200">
                     <img src="{{ $booking['shop_logo'] }}" alt="{{ $booking['shop_name'] }}"
                         class="w-full h-full object-cover">
                 </div>
                 <div class="flex-1">
                     <h3 class="font-semibold text-gray-900">{{ $booking['shop_name'] }}</h3>
                     <div class="flex items-center space-x-2">
                         <div class="flex items-center">
                             <svg class="h-4 w-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                 <path
                                     d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                             </svg>
                             <span class="text-sm text-gray-600 ml-1">{{ $booking['shop_rating'] }}</span>
                         </div>
                     </div>
                 </div>
             </div>

             <!-- Contact Info -->
             <div class="space-y-2 text-sm">
                 <div class="flex items-center space-x-2">
                     <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor">
                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                             d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                     </svg>
                     <span class="text-gray-700">{{ $booking['shop_address'] }}</span>
                 </div>
                 <div class="flex items-center space-x-2">
                     <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor">
                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                             d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                     </svg>
                     <span class="text-gray-700">{{ $booking['shop_phone'] }}</span>
                 </div>
             </div>
         </div>

         <!-- Important Notes -->
         <div class="bg-yellow-50 border border-yellow-200 rounded-lg px-4 py-4 mb-2">
             <div class="flex items-start space-x-2">
                 <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-yellow-600 mt-0.5" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor">
                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                         d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                 </svg>
                 <div>
                     <h3 class="font-medium text-yellow-800 mb-1">Recordatorios importantes</h3>
                     <ul class="text-sm text-yellow-700 space-y-1">
                         <li>• Llega 10 minutos antes de tu cita</li>
                         <li>• Puedes cancelar hasta 2 horas antes sin costo</li>
                         <li>• Trae una identificación válida</li>
                     </ul>
                 </div>
             </div>
         </div>

         <!-- Action Buttons -->
         <div class="space-y-3 px-4">
             <!-- Add to Calendar -->
             <button
                 class="w-full bg-white border border-gray-300 text-gray-700 py-3 px-4 rounded-lg font-medium flex items-center justify-center space-x-2 hover:bg-gray-50 transition-colors">
                 <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                     stroke="currentColor">
                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                         d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                 </svg>
                 <span>Agregar al calendario</span>
             </button>

             <!-- Contact Shop -->
             <button
                 class="w-full bg-purple-100 border border-purple-200 text-purple-700 py-3 px-4 rounded-lg font-medium flex items-center justify-center space-x-2 hover:bg-purple-200 transition-colors">
                 <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                     stroke="currentColor">
                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                         d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                 </svg>
                 <span>Contactar al establecimiento</span>
             </button>

             <!-- Back to Home -->
             <a href="{{ url('/') }}"
                 class="block w-full bg-purple-600 text-white py-3 px-4 rounded-lg font-medium text-center hover:bg-purple-700 transition-colors">
                 Volver al inicio
             </a>

             <!-- My Bookings -->
             <a href="{{ url('bookings/index') }}"
                 class="block w-full bg-white border border-purple-600 text-purple-600 py-3 px-4 rounded-lg font-medium text-center hover:bg-purple-50 transition-colors">
                 Ver mis reservas
             </a>
         </div>
     </main>


     <script src="{{ asset('js/app.js') }}"></script>
 </body>

 </html>
