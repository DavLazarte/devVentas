@props([
    'product' => null,
    'type' => 'articulo', // o 'servicio'
    'showShop' => true,
    'showFavorite' => true,
    'showAddButton' => true,
])

<section class="py-3 sm:py-4 px-3 sm:px-4">
    <h2 class="text-base sm:text-lg font-semibold mb-2 sm:mb-3">Productos Destacados</h2>
    <div class="grid grid-cols-2 gap-2 sm:gap-3 md:grid-cols-3 lg:grid-cols-4">
        @foreach ($productos as $producto)
            <a href="{{ route('product.show', ['type' => $producto instanceof \App\Models\Articulo ? 'articulo' : 'servicio', 'id' => $producto->id]) }}"
                class="block">
                <div
                    class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow">
                    <div class="relative h-32 sm:h-40 flex items-center justify-center bg-purple-50">
                        @if ($producto->imagen_url)
                            <img src="{{ $producto->imagen_url }}" alt="{{ $producto->nombre }}"
                                class="object-cover w-full h-full" loading="lazy">
                        @else
                            <span class="text-3xl font-bold text-purple-200 uppercase select-none">
                                {{ substr($producto->nombre, 0, 1) }}
                            </span>
                        @endif
                        @if ($producto->destacado)
                            <span
                                class="absolute top-1.5 left-1.5 sm:top-2 sm:left-2 bg-yellow-400 text-black text-xs px-1.5 sm:px-2 py-0.5 rounded-full">⭐
                                <span class="hidden xs:inline">Destacado</span></span>
                        @endif
                    </div>
                    <div class="p-2 sm:p-3">
                        <h3 class="font-medium text-xs sm:text-sm truncate">{{ $producto->nombre }}</h3>
                        <div class="flex justify-between items-center mt-1.5 sm:mt-2">
                            <span
                                class="text-purple-600 font-semibold text-sm sm:text-base">${{ number_format($producto->precio_unitario, 2) }}</span>
                        </div>
                    </div>
                </div>
    </div>
    </a>
    @endforeach
    </div>
</section>
