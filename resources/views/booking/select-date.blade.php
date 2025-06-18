   <!-- Sample Data -->
   @php
       $service = [
           'name' => 'Corte y Peinado Premium',
           'price' => 45.99,
           'duration' => '60 min',
           'image' => asset('images/haircut-service.jpg'),
           'shop_name' => 'Salón Elegance',
       ];

       $currentMonth = 'Enero 2024';
       $selectedDateTime = 'Viernes 15 de Enero, 10:30 AM';

       $availableDates = [
           ['day_name' => 'Hoy', 'day' => '12', 'month' => 'Ene', 'is_selected' => false, 'is_available' => true],
           ['day_name' => 'Mañ', 'day' => '13', 'month' => 'Ene', 'is_selected' => false, 'is_available' => true],
           ['day_name' => 'Jue', 'day' => '14', 'month' => 'Ene', 'is_selected' => false, 'is_available' => false],
           ['day_name' => 'Vie', 'day' => '15', 'month' => 'Ene', 'is_selected' => true, 'is_available' => true],
           ['day_name' => 'Sáb', 'day' => '16', 'month' => 'Ene', 'is_selected' => false, 'is_available' => true],
           ['day_name' => 'Dom', 'day' => '17', 'month' => 'Ene', 'is_selected' => false, 'is_available' => false],
           ['day_name' => 'Lun', 'day' => '18', 'month' => 'Ene', 'is_selected' => false, 'is_available' => true],
       ];

       $morningSlots = [
           ['time' => '9:00', 'is_selected' => false, 'is_available' => true],
           ['time' => '9:30', 'is_selected' => false, 'is_available' => false],
           ['time' => '10:00', 'is_selected' => false, 'is_available' => true],
           ['time' => '10:30', 'is_selected' => true, 'is_available' => true],
           ['time' => '11:00', 'is_selected' => false, 'is_available' => true],
           ['time' => '11:30', 'is_selected' => false, 'is_available' => true],
       ];

       $afternoonSlots = [
           ['time' => '14:00', 'is_selected' => false, 'is_available' => true],
           ['time' => '14:30', 'is_selected' => false, 'is_available' => true],
           ['time' => '15:00', 'is_selected' => false, 'is_available' => false],
           ['time' => '15:30', 'is_selected' => false, 'is_available' => true],
           ['time' => '16:00', 'is_selected' => false, 'is_available' => true],
           ['time' => '16:30', 'is_selected' => false, 'is_available' => true],
       ];

       $eveningSlots = [
           ['time' => '18:00', 'is_selected' => false, 'is_available' => true],
           ['time' => '18:30', 'is_selected' => false, 'is_available' => false],
           ['time' => '19:00', 'is_selected' => false, 'is_available' => true],
           ['time' => '19:30', 'is_selected' => false, 'is_available' => true],
       ];
   @endphp
   <!DOCTYPE html>
   <html lang="en">

   <head>
       <meta charset="UTF-8">
       <meta name="viewport" content="width=device-width, initial-scale=1.0">
       <title>Seleccionar Fecha y Hora - Tienda Dux</title>
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
                   <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-600" fill="none"
                       viewBox="0 0 24 24" stroke="currentColor">
                       <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                   </svg>
               </button>

               <!-- Title -->
               <h1 class="text-lg font-semibold text-gray-900">Fecha y Hora</h1>

               <!-- Progress -->
               <div class="text-sm text-gray-600">2/3</div>
           </div>
       </header>

       <main class="pb-24">
           <!-- Service Summary -->
           <div class="bg-white px-4 py-4 mb-2">
               <div class="flex items-center space-x-3">
                   <div class="w-12 h-12 rounded-lg overflow-hidden border border-gray-200">
                       <img src="{{ $service['image'] }}" alt="{{ $service['name'] }}"
                           class="w-full h-full object-cover">
                   </div>
                   <div class="flex-1">
                       <h3 class="font-semibold text-gray-900">{{ $service['name'] }}</h3>
                       <p class="text-sm text-gray-600">{{ $service['shop_name'] }} • {{ $service['duration'] }}</p>
                   </div>
                   <div class="text-right">
                       <p class="font-semibold text-purple-600">${{ number_format($service['price'], 2) }}</p>
                   </div>
               </div>
           </div>

           <!-- Date Selection -->
           <div class="bg-white px-4 py-4 mb-2">
               <h2 class="text-lg font-semibold text-gray-900 mb-4">Selecciona una fecha</h2>

               <!-- Month Navigation -->
               <div class="flex items-center justify-between mb-4">
                   <button class="p-2 rounded-full hover:bg-gray-100">
                       <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600" fill="none"
                           viewBox="0 0 24 24" stroke="currentColor">
                           <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                       </svg>
                   </button>
                   <h3 class="text-lg font-medium text-gray-900">{{ $currentMonth }}</h3>
                   <button class="p-2 rounded-full hover:bg-gray-100">
                       <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600" fill="none"
                           viewBox="0 0 24 24" stroke="currentColor">
                           <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                       </svg>
                   </button>
               </div>

               <!-- Horizontal Date Scroll -->
               <div class="flex overflow-x-auto space-x-3 pb-2 hide-scrollbar">
                   @foreach ($availableDates as $date)
                       <button
                           class="flex-shrink-0 w-16 py-3 rounded-lg text-center border transition-colors
                    {{ $date['is_selected'] ? 'bg-purple-600 text-white border-purple-600' : 'bg-white text-gray-700 border-gray-300 hover:border-purple-300' }}
                    {{ $date['is_available'] ? '' : 'opacity-50 cursor-not-allowed' }}">
                           <div class="text-xs font-medium">{{ $date['day_name'] }}</div>
                           <div class="text-lg font-bold">{{ $date['day'] }}</div>
                           <div class="text-xs">{{ $date['month'] }}</div>
                       </button>
                   @endforeach
               </div>
           </div>

           <!-- Time Selection -->
           <div class="bg-white px-4 py-4">
               <h2 class="text-lg font-semibold text-gray-900 mb-4">Horarios disponibles</h2>

               <!-- Morning -->
               <div class="mb-6">
                   <h3 class="text-sm font-medium text-gray-700 mb-3">Mañana</h3>
                   <div class="grid grid-cols-3 gap-2">
                       @foreach ($morningSlots as $slot)
                           <button
                               class="py-2 px-3 rounded-lg text-sm font-medium border transition-colors
                        {{ $slot['is_selected'] ? 'bg-purple-600 text-white border-purple-600' : 'bg-white text-gray-700 border-gray-300 hover:border-purple-300' }}
                        {{ $slot['is_available'] ? '' : 'opacity-50 cursor-not-allowed' }}">
                               {{ $slot['time'] }}
                           </button>
                       @endforeach
                   </div>
               </div>

               <!-- Afternoon -->
               <div class="mb-6">
                   <h3 class="text-sm font-medium text-gray-700 mb-3">Tarde</h3>
                   <div class="grid grid-cols-3 gap-2">
                       @foreach ($afternoonSlots as $slot)
                           <button
                               class="py-2 px-3 rounded-lg text-sm font-medium border transition-colors
                        {{ $slot['is_selected'] ? 'bg-purple-600 text-white border-purple-600' : 'bg-white text-gray-700 border-gray-300 hover:border-purple-300' }}
                        {{ $slot['is_available'] ? '' : 'opacity-50 cursor-not-allowed' }}">
                               {{ $slot['time'] }}
                           </button>
                       @endforeach
                   </div>
               </div>

               <!-- Evening -->
               <div>
                   <h3 class="text-sm font-medium text-gray-700 mb-3">Noche</h3>
                   <div class="grid grid-cols-3 gap-2">
                       @foreach ($eveningSlots as $slot)
                           <button
                               class="py-2 px-3 rounded-lg text-sm font-medium border transition-colors
                        {{ $slot['is_selected'] ? 'bg-purple-600 text-white border-purple-600' : 'bg-white text-gray-700 border-gray-300 hover:border-purple-300' }}
                        {{ $slot['is_available'] ? '' : 'opacity-50 cursor-not-allowed' }}">
                               {{ $slot['time'] }}
                           </button>
                       @endforeach
                   </div>
               </div>
           </div>
       </main>

       <!-- Fixed Bottom Continue Button -->
       <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 p-4 z-10">
           <div class="mb-3">
               <p class="text-sm text-gray-600">Fecha y hora seleccionada:</p>
               <p class="font-semibold text-gray-900">{{ $selectedDateTime }}</p>
           </div>
           <a href="{{ url('booking/info') }}"
               class="block w-full bg-purple-600 text-white py-3 px-4 rounded-lg font-medium text-lg text-center hover:bg-purple-700 transition-colors">
               Continuar
           </a>
       </div>



       <script src="{{ asset('js/app.js') }}"></script>
   </body>

   </html>
