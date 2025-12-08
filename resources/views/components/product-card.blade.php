<div>
    <a href="{{ route('product.show', ['type' => $type, 'id' => $type === 'articulo' ? $product->idarticulo : $product->idservicio]) }}"
        class="block">
        <div
            class="bg-white rounded-lg overflow-hidden shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
            <!-- Product Image -->
            <div class="aspect-square relative flex items-center justify-center bg-purple-50">
                @if ($product->imagen_url)
                    <img src="{{ $product->imagen_url }}" alt="{{ $product->nombre }}" class="w-full h-full object-cover">
                @else
                    <span class="text-4xl font-bold text-purple-200 uppercase select-none">
                        {{ substr($product->nombre, 0, 1) }}
                    </span>
                @endif

                <!-- Featured Badge -->
                @if ($product->destacado)
                    <div class="absolute top-2 left-2 bg-purple-600 text-white px-2 py-0.5 rounded text-xs font-medium">
                        Destacado
                    </div>
                @endif

                <!-- Favorite Button -->
                @if ($showFavorite)
                    <button
                        class="absolute bottom-2 right-2 w-8 h-8 bg-white rounded-full shadow-md flex items-center justify-center hover:bg-gray-50"
                        onclick="event.preventDefault();">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                        </svg>
                    </button>
                @endif
            </div>

            <!-- Product Info -->
            <div class="p-2.5 sm:p-3">
                <!-- Shop Name -->
                @if ($showShop && isset($product->local))
                    <p class="text-xs text-gray-500 mb-1 truncate">{{ $product->local->nombre }}</p>
                @endif

                <!-- Product Name -->
                <h3 class="font-medium text-xs sm:text-sm text-gray-900 mb-1.5 line-clamp-2 leading-tight">
                    {{ $product->nombre }}
                </h3>

                <!-- Price -->
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center flex-wrap gap-1">
                        @if (isset($product->tiene_variantes) && $product->tiene_variantes)
                            <span class="text-gray-500 text-xs">Desde</span>
                            <span class="text-purple-600 font-semibold text-sm sm:text-base">
                                ${{ number_format($product->precio_minimo ?? $product->precio_unitario, 2) }}
                            </span>
                        @else
                            <span class="text-purple-600 font-semibold text-sm sm:text-base">
                                ${{ number_format($product->precio_unitario, 2) }}
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Quick Add Button -->
                @if ($showAddButton && $type === 'articulo' && (!isset($product->tiene_variantes) || !$product->tiene_variantes))
                    <div class="flex flex-col space-y-1.5 sm:space-y-2" onclick="event.preventDefault();">
                        <!-- Cantidad -->
                        <div class="flex items-center justify-between">
                            <span class="text-xs sm:text-sm text-gray-600">Cantidad:</span>
                            <div class="flex items-center space-x-1.5 sm:space-x-2">
                                <button
                                    class="w-6 h-6 sm:w-7 sm:h-7 bg-gray-100 text-gray-600 rounded-full flex items-center justify-center hover:bg-gray-200 transition-colors active:scale-95"
                                    wire:click="updateQuantity({{ $product->idarticulo }}, {{ $quantity - 1 }})">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 sm:h-4 sm:w-4" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M20 12H4" />
                                    </svg>
                                </button>

                                <span
                                    class="text-xs sm:text-sm font-medium w-6 sm:w-8 text-center">{{ $quantity }}</span>

                                <button
                                    class="w-6 h-6 sm:w-7 sm:h-7 bg-gray-100 text-gray-600 rounded-full flex items-center justify-center hover:bg-gray-200 transition-colors active:scale-95"
                                    wire:click="updateQuantity({{ $product->idarticulo }}, {{ $quantity + 1 }})">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 sm:h-4 sm:w-4" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 4v16m8-8H4" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Agregar al Carrito -->
                        <button
                            class="w-full bg-purple-600 text-white py-1.5 sm:py-2 px-2 sm:px-3 rounded-lg flex items-center justify-center space-x-1 sm:space-x-1.5 hover:bg-purple-700 transition-colors text-xs sm:text-sm font-medium active:scale-95"
                            wire:click="addToCart({{ $type === 'articulo' ? $product->idarticulo : $product->idservicio }}, {{ $quantity }})"
                            wire:loading.attr="disabled" wire:target="addToCart">

                            {{-- LOADER --}}
                            <span wire:loading wire:target="addToCart"
                                class="flex items-center space-x-1 sm:space-x-1.5">
                                <svg class="animate-spin h-3 w-3 sm:h-4 sm:w-4 text-white"
                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                                <span class="hidden xs:inline">Agregando...</span>
                                <span class="xs:hidden">...</span>
                            </span>

                            {{-- CONTENIDO NORMAL --}}
                            <span wire:loading.remove wire:target="addToCart"
                                class="flex items-center space-x-1 sm:space-x-1.5">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 sm:h-4 sm:w-4" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                <span>Agregar</span>
                            </span>
                        </button>
                    </div>
                @else
                    <a
                        href="{{ route('product.show', ['type' => $type, 'id' => $type === 'articulo' ? $product->idarticulo : $product->idservicio]) }}">
                        <button
                            class="w-full bg-green-600 text-white py-2 px-3 rounded-lg flex items-center justify-center space-x-1.5 hover:bg-green-700 transition-colors text-sm font-medium">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span>{{ $type === 'articulo' ? 'Ver opciones' : 'Reservar' }}</span>
                        </button>
                    </a>
                @endif

            </div>
        </div>
    </a>
</div>
