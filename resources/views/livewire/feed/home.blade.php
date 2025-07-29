<div class="pb-20">
    <x-header />

    <!-- Search Bar -->
    <div class="bg-white py-3 px-4 mb-2 shadow-sm">
        <x-search-bar
            placeholder="Buscar tiendas, productos o servicios..."
            model="globalSearch"
        />
    </div>
    <x-adv-slider/>

    <x-categories-carousel :categories="$categorias" />
    <x-promo-banner />
    <x-shops-carousel :shops="$localesDestacados"/>

    <!-- Productos Destacados -->
    <div class="container mx-auto px-4 py-8">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Productos Destacados</h2>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            @foreach($productosDestacados as $producto)
                <livewire:product-card
                    :product="$producto"
                    :type="'articulo'"
                    :showShop="true"
                    :showFavorite="true"
                    :showAddButton="true"
                    :wire:key="'product-'.$producto->idarticulo"
                />
            @endforeach
        </div>
    </div>

    <x-recommendations />
    {{-- <livewire:cart /> --}}
    <livewire:footer-menu />
</div>
