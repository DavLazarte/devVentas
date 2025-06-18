@props([
    'product' => null,
    'type' => 'articulo', // o 'servicio'
    'showShop' => true,
    'showFavorite' => true,
    'showAddButton' => true
])

<section class="py-4 px-4">
    <h2 class="text-lg font-semibold mb-3">Productos Destacados</h2>
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4">
        @foreach ($productos as $producto)
        <a href="{{ route('product.show', ['type' => $producto instanceof \App\Models\Articulo ? 'articulo' : 'servicio', 'id' => $producto->id]) }}" class="block">
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow">
                <div class="relative h-40">
                    <img
                        src="{{ $producto->imagen_url }}"
                        alt="{{ $producto->nombre }}"
                        class="object-cover w-full h-full"
                        loading="lazy"
                    >
                    @if($producto->destacado)
                        <span class="absolute top-2 left-2 bg-yellow-400 text-black text-xs px-2 py-0.5 rounded-full">⭐ Destacado</span>
                    @endif
                </div>
                <div class="p-3">
                    <h3 class="font-medium text-sm truncate">{{ $producto->nombre }}</h3>
                    <div class="flex justify-between items-center mt-2">
                        <span class="text-purple-600 font-semibold">${{ number_format($producto->precio_unitario, 2) }}</span>
                        {{-- <span class="text-xs text-gray-500">{{ $producto->stock }} disponibles</span> --}}
                    </div>
                </div>
            </div>
        </a>
        @endforeach
    </div>
</section>
