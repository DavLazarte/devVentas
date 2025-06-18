    <!-- Sample Data -->
    @php
        $activeType = request('type', 'all');
        $activeTypeName = 'Todos';

        $typeFilters = [
            ['name' => 'Todos', 'slug' => 'all', 'count' => 89],
            ['name' => 'Salón de Belleza', 'slug' => 'beauty-salon', 'count' => 23],
            ['name' => 'Restaurante', 'slug' => 'restaurant', 'count' => 18],
            ['name' => 'Spa', 'slug' => 'spa', 'count' => 12],
            ['name' => 'Barbería', 'slug' => 'barbershop', 'count' => 15],
            ['name' => 'Tienda', 'slug' => 'shop', 'count' => 21],
        ];

        $shops = [
            [
                'id' => 1,
                'name' => 'Salón Elegance',
                'type' => 'Salón de Belleza',
                'logo' => asset('images/salon-logo.png'),
                'rating' => 4.8,
                'review_count' => 127,
                'is_open' => true,
                'is_favorite' => false,
                'is_verified' => true,
                'description' =>
                    'Salón de belleza especializado en cortes modernos, coloración y tratamientos capilares con productos premium.',
                'distance' => '0.8 km',
                'delivery_time' => null,
                'total_products' => 12,
            ],
            [
                'id' => 2,
                'name' => 'Nail Studio Pro',
                'type' => 'Estudio de Uñas',
                'logo' => asset('images/nail-studio-logo.jpg'),
                'rating' => 4.6,
                'review_count' => 89,
                'is_open' => true,
                'is_favorite' => true,
                'is_verified' => false,
                'description' =>
                    'Especialistas en manicura, pedicura y nail art. Diseños únicos y productos de alta calidad.',
                'distance' => '1.2 km',
                'delivery_time' => null,
                'total_products' => 8,
            ],
            [
                'id' => 3,
                'name' => 'Spa Relax Center',
                'type' => 'Spa',
                'logo' => asset('images/spa-logo.png'),
                'rating' => 4.9,
                'review_count' => 203,
                'is_open' => false,
                'is_favorite' => false,
                'is_verified' => true,
                'description' =>
                    'Centro de relajación y bienestar. Masajes terapéuticos, tratamientos faciales y corporales.',
                'distance' => '2.1 km',
                'delivery_time' => null,
                'total_products' => 15,
            ],
            [
                'id' => 4,
                'name' => 'Hair Experts',
                'type' => 'Peluquería',
                'logo' => asset('images/salon-logo.jpg'),
                'rating' => 4.7,
                'review_count' => 156,
                'is_open' => true,
                'is_favorite' => false,
                'is_verified' => true,
                'description' =>
                    'Peluquería unisex con estilistas especializados en tendencias actuales y técnicas avanzadas.',
                'distance' => '0.5 km',
                'delivery_time' => null,
                'total_products' => 10,
            ],
            [
                'id' => 5,
                'name' => 'Beauty Center',
                'type' => 'Centro de Belleza',
                'logo' => asset('images/salon-logo.jpg'),
                'rating' => 4.5,
                'review_count' => 94,
                'is_open' => true,
                'is_favorite' => true,
                'is_verified' => false,
                'description' =>
                    'Centro integral de belleza. Servicios de depilación, tratamientos faciales y corporales.',
                'distance' => '1.8 km',
                'delivery_time' => null,
                'total_products' => 18,
            ],
            [
                'id' => 6,
                'name' => 'Barber Shop Classic',
                'type' => 'Barbería',
                'logo' => asset('images/salon-logo.jpg'),
                'rating' => 4.4,
                'review_count' => 67,
                'is_open' => false,
                'is_favorite' => false,
                'is_verified' => false,
                'description' => 'Barbería tradicional con servicios de corte clásico, afeitado y arreglo de barba.',
                'distance' => '3.2 km',
                'delivery_time' => null,
                'total_products' => 6,
            ],
        ];

        $totalShops = count($shops);
        $hasMoreShops = true;
    @endphp

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
         <h1 class="text-lg font-semibold text-gray-900 truncate mx-4">Tiendas y Servicios</h1>

         <!-- Map View Toggle -->
         <button class="p-2 rounded-full hover:bg-gray-100">
             <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-600" fill="none" viewBox="0 0 24 24"
                 stroke="currentColor">
                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                     d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                     d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
             </svg>
         </button>
     </div>
 </header>

 <main class="pb-6">
     <!-- Filter Bar -->
     <div class="bg-white py-3 px-4 mb-2 shadow-sm">
         <!-- Filter Chips -->
         <div class="flex overflow-x-auto space-x-2 pb-1 hide-scrollbar mb-3">
             @foreach ($typeFilters as $filter)
                 <button
                     class="flex-shrink-0 px-4 py-2 rounded-full text-sm font-medium border transition-colors
                {{ $filter['slug'] === $activeType ? 'bg-purple-600 text-white border-purple-600' : 'bg-white text-gray-700 border-gray-300 hover:border-purple-300' }}">
                     {{ $filter['name'] }}
                     @if ($filter['count'] > 0)
                         <span class="ml-1 text-xs opacity-75">({{ $filter['count'] }})</span>
                     @endif
                 </button>
             @endforeach
         </div>

         <!-- Additional Filters -->
         <div class="flex space-x-2">
             <!-- Rating Filter -->
             <button
                 class="flex items-center space-x-1 px-3 py-1.5 rounded-full border border-gray-300 text-sm text-gray-600 hover:border-purple-300">
                 <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-yellow-400" fill="currentColor"
                     viewBox="0 0 20 20">
                     <path
                         d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                 </svg>
                 <span>4.0+</span>
             </button>

             <!-- Distance Filter -->
             <button
                 class="flex items-center space-x-1 px-3 py-1.5 rounded-full border border-gray-300 text-sm text-gray-600 hover:border-purple-300">
                 <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                     stroke="currentColor">
                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                         d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                 </svg>
                 <span>Cerca</span>
             </button>

             <!-- Open Now Filter -->
             <button
                 class="flex items-center space-x-1 px-3 py-1.5 rounded-full border border-gray-300 text-sm text-gray-600 hover:border-purple-300">
                 <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                 <span>Abierto</span>
             </button>
         </div>
     </div>

     <!-- Results Header -->
     <div class="px-4 py-3 flex items-center justify-between">
         <div class="flex items-center space-x-2">
             <span class="text-gray-600 text-sm">{{ $totalShops }} tiendas</span>
             @if ($activeType !== 'all')
                 <span class="text-gray-400 text-sm">•</span>
                 <span class="text-purple-600 text-sm font-medium">{{ $activeTypeName }}</span>
             @endif
         </div>

         <!-- Sort Dropdown -->
         <div class="relative">
             <button class="flex items-center space-x-1 text-sm text-gray-600 hover:text-gray-900">
                 <span>Ordenar</span>
                 <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                     stroke="currentColor">
                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                 </svg>
             </button>
         </div>
     </div>

     <!-- Shops List -->
     <div class="px-4">
         @if (count($shops) > 0)
             <div class="space-y-3">
                 @foreach ($shops as $shop)
                     <div
                         class="bg-white rounded-lg p-4 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                         <div class="flex space-x-3">
                             <!-- Shop Logo -->
                             <div class="flex-shrink-0">
                                 <div class="w-16 h-16 rounded-full overflow-hidden border border-gray-200">
                                     <img src="{{ $shop['logo'] }}" alt="{{ $shop['name'] }}"
                                         class="w-full h-full object-cover">
                                 </div>
                             </div>

                             <!-- Shop Info -->
                             <div class="flex-1 min-w-0">
                                 <!-- Header Row -->
                                 <div class="flex items-start justify-between mb-1">
                                     <div class="flex-1 min-w-0">
                                         <h3 class="font-semibold text-gray-900 truncate">{{ $shop['name'] }}</h3>
                                         <div class="flex items-center space-x-2 mt-0.5">
                                             <span
                                                 class="bg-purple-100 text-purple-700 px-2 py-0.5 rounded text-xs font-medium">
                                                 {{ $shop['type'] }}
                                             </span>
                                             @if ($shop['is_verified'])
                                                 <span
                                                     class="bg-blue-100 text-blue-700 px-2 py-0.5 rounded text-xs font-medium flex items-center">
                                                     <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-0.5"
                                                         fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                         <path stroke-linecap="round" stroke-linejoin="round"
                                                             stroke-width="2"
                                                             d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                     </svg>
                                                     Verificado
                                                 </span>
                                             @endif
                                         </div>
                                     </div>

                                     <!-- Favorite Button -->
                                     <button class="p-1 rounded-full hover:bg-gray-100 ml-2">
                                         <svg xmlns="http://www.w3.org/2000/svg"
                                             class="h-5 w-5 {{ $shop['is_favorite'] ? 'text-red-500 fill-current' : 'text-gray-400' }}"
                                             fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                 d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                         </svg>
                                     </button>
                                 </div>

                                 <!-- Rating and Status -->
                                 <div class="flex items-center space-x-3 mb-2">
                                     <div class="flex items-center space-x-1">
                                         <div class="flex items-center">
                                             @for ($i = 1; $i <= 5; $i++)
                                                 <svg class="h-3 w-3 {{ $i <= $shop['rating'] ? 'text-yellow-400' : 'text-gray-300' }}"
                                                     fill="currentColor" viewBox="0 0 20 20">
                                                     <path
                                                         d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                 </svg>
                                             @endfor
                                         </div>
                                         <span class="text-sm text-gray-600">{{ $shop['rating'] }}</span>
                                     </div>
                                     <span class="text-gray-400">•</span>
                                     <span class="text-sm text-gray-600">({{ $shop['review_count'] }}
                                         reseñas)</span>
                                     <span class="text-gray-400">•</span>
                                     <span class="text-sm {{ $shop['is_open'] ? 'text-green-600' : 'text-red-600' }}">
                                         {{ $shop['is_open'] ? 'Abierto' : 'Cerrado' }}
                                     </span>
                                 </div>

                                 <!-- Description -->
                                 <p class="text-gray-600 text-sm line-clamp-2 mb-3">{{ $shop['description'] }}</p>

                                 <!-- Footer Info -->
                                 <div class="flex items-center justify-between">
                                     <div class="flex items-center space-x-3 text-xs text-gray-500">
                                         @if ($shop['distance'])
                                             <div class="flex items-center space-x-1">
                                                 <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3"
                                                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                     <path stroke-linecap="round" stroke-linejoin="round"
                                                         stroke-width="2"
                                                         d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                 </svg>
                                                 <span>{{ $shop['distance'] }}</span>
                                             </div>
                                         @endif

                                         @if ($shop['delivery_time'])
                                             <div class="flex items-center space-x-1">
                                                 <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3"
                                                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                     <path stroke-linecap="round" stroke-linejoin="round"
                                                         stroke-width="2"
                                                         d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                 </svg>
                                                 <span>{{ $shop['delivery_time'] }}</span>
                                             </div>
                                         @endif

                                         <span>{{ $shop['total_products'] }} productos</span>
                                     </div>

                                     <!-- Quick Actions -->
                                     <div class="flex space-x-2">
                                         <button class="text-purple-600 text-sm font-medium">Ver tienda</button>
                                     </div>
                                 </div>
                             </div>
                         </div>
                     </div>
                 @endforeach
             </div>

             <!-- Load More Button -->
             @if ($hasMoreShops)
                 <div class="mt-6 text-center">
                     <button
                         class="bg-white border border-gray-300 text-gray-700 px-6 py-2 rounded-lg font-medium hover:bg-gray-50 transition-colors">
                         Cargar más tiendas
                     </button>
                 </div>
             @endif
         @else
             <!-- Empty State -->
             <div class="text-center py-12">
                 <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-300 mx-auto mb-4" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor">
                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                         d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                 </svg>
                 <h3 class="text-lg font-medium text-gray-900 mb-2">No se encontraron tiendas</h3>
                 <p class="text-gray-600 mb-4">Intenta ajustar tus filtros o buscar en otra área</p>
                 <button class="bg-purple-600 text-white px-4 py-2 rounded-lg font-medium">
                     Limpiar filtros
                 </button>
             </div>
         @endif
     </div>
 </main>
