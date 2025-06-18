<div>
    <a href="{{ route('product.show', ['type' => $type, 'id' => $type === 'articulo' ? $product->idarticulo : $product->idservicio]) }}" class="block">
        <div class="bg-white rounded-lg overflow-hidden shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
            <!-- Product Image -->
            <div class="aspect-square relative">
                <img src="{{ $product->imagen_url }}" alt="{{ $product->nombre }}"
                    class="w-full h-full object-cover">

                <!-- Featured Badge -->
                @if($product->destacado)
                    <div class="absolute top-2 left-2 bg-purple-600 text-white px-2 py-0.5 rounded text-xs font-medium">
                        Destacado
                    </div>
                @endif

                <!-- Favorite Button -->
                @if($showFavorite)
                    <button class="absolute bottom-2 right-2 w-8 h-8 bg-white rounded-full shadow-md flex items-center justify-center hover:bg-gray-50" onclick="event.preventDefault();">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                        </svg>
                    </button>
                @endif
            </div>

            <!-- Product Info -->
            <div class="p-3">
                <!-- Shop Name -->
                @if($showShop && isset($product->local))
                    <p class="text-xs text-gray-500 mb-1 truncate">{{ $product->local->nombre }}</p>
                @endif

                <!-- Product Name -->
                <h3 class="font-medium text-sm text-gray-900 mb-1 line-clamp-2 leading-tight">
                    {{ $product->nombre }}
                </h3>

                <!-- Price -->
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center space-x-1">
                        <span class="text-purple-600 font-semibold text-sm">
                            ${{ number_format($product->precio_unitario, 2) }}
                        </span>
                    </div>
                </div>

                <!-- Quick Add Button -->
                @if($showAddButton)
                    <div class="flex flex-col space-y-2" onclick="event.preventDefault();">
                        <!-- Cantidad -->
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Cantidad:</span>
                            <div class="flex items-center space-x-2">
                                <button
                                    class="w-7 h-7 bg-gray-100 text-gray-600 rounded-full flex items-center justify-center hover:bg-gray-200 transition-colors"
                                    wire:click="updateQuantity({{ $product->idarticulo }}, {{ $quantity - 1 }})"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                    </svg>
                                </button>

                                <span class="text-sm font-medium w-8 text-center">{{ $quantity }}</span>

                                <button
                                    class="w-7 h-7 bg-gray-100 text-gray-600 rounded-full flex items-center justify-center hover:bg-gray-200 transition-colors"
                                    wire:click="updateQuantity({{ $product->idarticulo }}, {{ $quantity + 1 }})"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Agregar al Carrito -->
                        <button
                            class="w-full bg-purple-600 text-white py-1.5 px-3 rounded-lg flex items-center justify-center space-x-1.5 hover:bg-purple-700 transition-colors text-sm"
                            wire:click="addToCart({{ $type === 'articulo' ? $product->idarticulo : $product->idservicio }}, {{ $quantity }})"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <span>Agregar</span>
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </a>
</div>
