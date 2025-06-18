  <!-- Sample Data -->
  @php
      $service = [
          'name' => 'Corte y Peinado Premium',
          'price' => 45.99,
          'duration' => '60 min',
          'image' => asset('images/haircut-service.jpg'),
          'shop_name' => 'Salón Elegance',
          'shop_logo' => asset('images/salon-logo.jpg'),
          'shop_rating' => 4.8,
          'shop_address' => 'Av. Libertador 1234',
          'staff_name' => 'María González',
          'rating' => 4.9,
          'review_count' => 127,
          'description' =>
              'Servicio completo de corte y peinado con productos premium. Incluye lavado, corte personalizado según tu rostro, peinado y acabado con productos de alta calidad. Nuestros estilistas profesionales te ayudarán a conseguir el look perfecto.',
          'includes' => [
              'Consulta personalizada',
              'Lavado con productos premium',
              'Corte según tipo de rostro',
              'Peinado y acabado',
              'Consejos de mantenimiento',
          ],
      ];
  @endphp
  <!DOCTYPE html>
  <html lang="en">

  <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>{{ $service['name'] }} - Reservar - Tienda Dux</title>
      <!-- tailwind -->
      <link href="{{ asset('css/app.css') }}" rel="stylesheet">

      {{-- @livewireStyles --}}
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
              <h1 class="text-lg font-semibold text-gray-900 truncate mx-4">Reservar Servicio</h1>

              <!-- Share Button -->
              <button class="p-2 rounded-full hover:bg-gray-100">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-600" fill="none"
                      viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z" />
                  </svg>
              </button>
          </div>
      </header>

      <main class="pb-24">
          <!-- Service Image -->
          <div class="relative bg-white">
              <div class="aspect-video relative">
                  <img src="{{ $service['image'] }}" alt="{{ $service['name'] }}" class="w-full h-full object-cover">

                  <!-- Duration Badge -->
                  <div class="absolute top-4 left-4 bg-black bg-opacity-70 text-white px-3 py-1 rounded-full text-sm">
                      {{ $service['duration'] }}
                  </div>
              </div>
          </div>

          <!-- Service Info -->
          <div class="bg-white px-4 py-4 mb-2">
              <!-- Service Name and Price -->
              <div class="flex items-start justify-between mb-3">
                  <div class="flex-1">
                      <h1 class="text-xl font-bold text-gray-900 mb-1">{{ $service['name'] }}</h1>
                      <p class="text-sm text-gray-600">{{ $service['shop_name'] }}</p>
                  </div>
                  <div class="text-right">
                      <span class="text-2xl font-bold text-purple-600">${{ number_format($service['price'], 2) }}</span>
                  </div>
              </div>

              <!-- Service Details -->
              <div class="grid grid-cols-2 gap-4 mb-4">
                  <div class="flex items-center space-x-2">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none"
                          viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                      <span class="text-sm text-gray-600">{{ $service['duration'] }}</span>
                  </div>
                  <div class="flex items-center space-x-2">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none"
                          viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                      </svg>
                      <span class="text-sm text-gray-600">{{ $service['staff_name'] }}</span>
                  </div>
              </div>

              <!-- Rating -->
              <div class="flex items-center space-x-2 mb-4">
                  <div class="flex items-center">
                      @for ($i = 1; $i <= 5; $i++)
                          <svg class="h-4 w-4 {{ $i <= $service['rating'] ? 'text-yellow-400' : 'text-gray-300' }}"
                              fill="currentColor" viewBox="0 0 20 20">
                              <path
                                  d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                          </svg>
                      @endfor
                  </div>
                  <span class="text-sm text-gray-600">{{ $service['rating'] }} ({{ $service['review_count'] }}
                      reseñas)</span>
              </div>
          </div>

          <!-- Description -->
          <div class="bg-white px-4 py-4 mb-2">
              <h2 class="text-lg font-semibold text-gray-900 mb-3">Descripción del servicio</h2>
              <p class="text-gray-700 leading-relaxed">{{ $service['description'] }}</p>
          </div>

          <!-- What's Included -->
          <div class="bg-white px-4 py-4 mb-2">
              <h2 class="text-lg font-semibold text-gray-900 mb-3">Incluye</h2>
              <div class="space-y-2">
                  @foreach ($service['includes'] as $item)
                      <div class="flex items-center space-x-2">
                          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-500" fill="none"
                              viewBox="0 0 24 24" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M5 13l4 4L19 7" />
                          </svg>
                          <span class="text-sm text-gray-700">{{ $item }}</span>
                      </div>
                  @endforeach
              </div>
          </div>

          <!-- Shop Info -->
          <div class="bg-white px-4 py-4">
              <h2 class="text-lg font-semibold text-gray-900 mb-3">Acerca del establecimiento</h2>
              <div class="flex items-center space-x-3">
                  <div class="w-12 h-12 rounded-full overflow-hidden border border-gray-200">
                      <img src="{{ $service['shop_logo'] }}" alt="{{ $service['shop_name'] }}"
                          class="w-full h-full object-cover">
                  </div>
                  <div class="flex-1">
                      <h3 class="font-semibold text-gray-900">{{ $service['shop_name'] }}</h3>
                      <div class="flex items-center space-x-2">
                          <div class="flex items-center">
                              <svg class="h-4 w-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                  <path
                                      d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                              </svg>
                              <span class="text-sm text-gray-600 ml-1">{{ $service['shop_rating'] }}</span>
                          </div>
                          <span class="text-sm text-gray-500">•</span>
                          <span class="text-sm text-gray-600">{{ $service['shop_address'] }}</span>
                      </div>
                  </div>
                  <button class="text-purple-600 font-medium text-sm border border-purple-600 px-3 py-1 rounded-md">
                      Ver tienda
                  </button>
              </div>
          </div>
      </main>

      <!-- Fixed Bottom Book Button -->
      <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 p-4 z-10">
          <div class="flex items-center justify-between mb-3">
              <div>
                  <p class="text-sm text-gray-600">Precio del servicio</p>
                  <p class="text-xl font-bold text-purple-600">${{ number_format($service['price'], 2) }}</p>
              </div>
              <div class="text-right">
                  <p class="text-xs text-gray-500">Duración</p>
                  <p class="text-sm font-medium text-gray-900">{{ $service['duration'] }}</p>
              </div>
          </div>
          {{-- <a href="{{ route('booking.datetime') }}" --}}
          <a href="{{ url('/booking/datetime') }}"
              class="block w-full bg-purple-600 text-white py-3 px-4 rounded-lg font-medium text-lg text-center hover:bg-purple-700 transition-colors">
              Reservar ahora
          </a>
      </div>



      <script src="{{ asset('js/app.js') }}"></script>
  </body>

  </html>
