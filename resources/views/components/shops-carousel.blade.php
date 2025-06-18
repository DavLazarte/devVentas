<!-- Featured Shops -->
@props(['shops' => []])
<section class="py-4 px-4">
    <h2 class="text-lg font-semibold mb-3">Tiendas Destacadas</h2>
    <div class="flex overflow-x-auto space-x-4 pb-2 hide-scrollbar">
        @foreach ($shops as $shop)
            <a href="{{ route('store.show', $shop->slug) }}" class="flex flex-col items-center min-w-[80px] max-w-[80px] hover:opacity-80 transition-opacity">
                <div class="w-16 h-16 rounded-full border border-gray-200 overflow-hidden mb-1">
                    @php
                        $defaultImage = asset('images/store-default.png');
                        $logoPath = !empty($shop->foto_logo) && file_exists(public_path('storage/' . $shop->foto_logo))
                            ? Voyager::image($shop->foto_logo)
                            : $defaultImage;
                        // Debug information
                        echo "<!-- Debug: foto_logo = " . ($shop->foto_logo ?? 'null') . " -->";
                        echo "<!-- Debug: logoPath = " . $logoPath . " -->";
                    @endphp
                    <img src="{{ $logoPath }}"
                        alt="{{ $shop['nombre'] }}" class="object-cover w-full h-full">
                </div>
                <h3 class="text-xs font-medium text-center truncate w-full">{{ $shop['nombre'] }}</h3>
                <div class="flex items-center">
                    <span class="text-xs text-yellow-500">★</span>
                    <span class="text-xs ml-0.5">{{ $shop['rating_promedio'] }}</span>
                </div>
            </a>
        @endforeach
    </div>
</section>
