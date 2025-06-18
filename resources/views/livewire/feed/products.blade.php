<div>
    <x-header />

    <main class="pb-20">
        <!-- Search Bar -->
        <div class="bg-white py-3 px-4 mb-2 shadow-sm">
            <x-search-bar
                placeholder="Buscar productos o servicios..."
                model="search"
            />
        </div>

        <!-- Tabs -->
        <div class="bg-white px-4 py-3 mb-2 shadow-sm">
            <div class="flex space-x-1 bg-gray-100 rounded-lg p-1">
                <button
                    wire:click="$set('activeTab', 'products')"
                    class="flex-1 py-2 px-3 rounded-md text-sm font-medium transition-colors {{ $activeTab === 'products' ? 'bg-white text-purple-600 shadow-sm' : 'text-gray-600 hover:text-gray-900' }}"
                >
                    Productos
                </button>
                <button
                    wire:click="$set('activeTab', 'services')"
                    class="flex-1 py-2 px-3 rounded-md text-sm font-medium transition-colors {{ $activeTab === 'services' ? 'bg-white text-purple-600 shadow-sm' : 'text-gray-600 hover:text-gray-900' }}"
                >
                    Servicios
                </button>
            </div>
        </div>

        <!-- Results Header -->
        <div class="px-4 py-3 flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <span class="text-gray-600 text-sm">
                    {{ $activeTab === 'products' ? $products->count() : $services->count() }}
                    {{ $activeTab === 'products' ? 'productos' : 'servicios' }}
                </span>
            </div>

            <!-- Sort Dropdown -->
            <div class="relative">
                <button class="flex items-center space-x-1 text-sm text-gray-600 hover:text-gray-900">
                    <span>Ordenar</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Items Grid -->
        <div class="px-4">
            @if($activeTab === 'products' && $products->count() > 0)
                <div class="grid grid-cols-2 gap-3">
                    @foreach($products as $product)
                        <x-product-card :product="$product" />
                    @endforeach
                </div>
            @elseif($activeTab === 'services' && $services->count() > 0)
                <div class="grid grid-cols-2 gap-3">
                    @foreach($services as $service)
                        <x-product-card :product="$service" />
                    @endforeach
                </div>
            @else
                <div class="text-center py-12">
                    <p class="text-gray-500 text-lg">No se encontraron {{ $activeTab === 'products' ? 'productos' : 'servicios' }} que coincidan con tu búsqueda.</p>
                </div>
            @endif

            <!-- Load More Button -->
            @if($hasMorePages)
                <div class="mt-6 text-center">
                    <button
                        wire:click="loadMore"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span wire:loading.remove>Cargar más {{ $activeTab === 'products' ? 'productos' : 'servicios' }}</span>
                        <span wire:loading>Cargando...</span>
                    </button>
                </div>
            @endif
        </div>
    </main>
    <livewire:footer-menu />
</div>
