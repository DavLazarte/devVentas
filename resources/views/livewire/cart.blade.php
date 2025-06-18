<div>
    <div class="bg-white rounded-lg shadow-sm p-4">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Carrito de Compras</h3>

        @if(count($items) > 0)
            <div class="space-y-4">
                @foreach($items as $key => $item)
                    <div class="flex items-center space-x-4 p-2 bg-gray-50 rounded-lg">
                        <!-- Imagen -->
                        <div class="w-16 h-16 rounded-lg overflow-hidden flex-shrink-0">
                            <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" class="w-full h-full object-cover">
                        </div>

                        <!-- Información -->
                        <div class="flex-grow">
                            <h3 class="text-sm font-medium text-gray-900">{{ $item['name'] }}</h3>
                            @if($item['shop'])
                                <p class="text-xs text-gray-500">{{ $item['shop'] }}</p>
                            @endif
                            <p class="text-sm text-purple-600 font-medium">${{ number_format($item['price'], 2) }}</p>
                        </div>

                        <!-- Controles de cantidad -->
                        <div class="flex items-center space-x-2">
                            <button wire:click="updateQuantity('{{ $key }}', {{ $item['quantity'] - 1 }})" class="w-6 h-6 bg-gray-200 rounded-full flex items-center justify-center hover:bg-gray-300">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                </svg>
                            </button>
                            <span class="text-sm font-medium w-8 text-center">{{ $item['quantity'] }}</span>
                            <button wire:click="updateQuantity('{{ $key }}', {{ $item['quantity'] + 1 }})" class="w-6 h-6 bg-gray-200 rounded-full flex items-center justify-center hover:bg-gray-300">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                            </button>
                        </div>

                        <!-- Eliminar -->
                        <button wire:click="removeItem('{{ $key }}')" class="text-gray-400 hover:text-red-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </div>
                @endforeach

                <div class="border-t border-gray-200 pt-4 mt-4">
                    <div class="flex justify-between items-center">
                        <span class="text-lg font-semibold text-gray-900">Total</span>
                        <span class="text-lg font-semibold text-purple-600">${{ number_format($total, 2) }}</span>
                    </div>
                </div>
            </div>
        @else
            <div class="text-center py-8">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Carrito vacío</h3>
                <p class="mt-1 text-sm text-gray-500">Agrega productos o servicios para comenzar a comprar.</p>
            </div>
        @endif
    </div>
</div>
