<div>
    <x-header />

    <main class="pb-20">
        <!-- Search Bar -->
        <div class="bg-white py-3 px-4 mb-2 shadow-sm">
            <x-search-bar
                placeholder="Buscar tiendas..."
                model="search"
            />
        </div>

        <!-- Filter Bar -->
        <x-subcategories-slider
            :subcategories="$subcategories"
            :selectedSubcategory="$selectedSubcategory"
        />

        <!-- Results Header -->
        <div class="px-4 py-3 flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <span class="text-gray-600 text-sm">{{ $stores->count() }} tiendas</span>
                @if($selectedSubcategory)
                    <span class="text-gray-400 text-sm">•</span>
                    <span class="text-purple-600 text-sm font-medium">
                        {{ $subcategories->firstWhere('id', $selectedSubcategory)->name }}
                    </span>
                @endif
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

        <!-- Shops List -->
        <div class="px-4">
            @if($stores->count() > 0)
                <div class="space-y-3">
                    @foreach($stores as $store)
                        <x-card-store :store="$store" />
                    @endforeach
                </div>

                <!-- Load More Button -->
                @if($hasMorePages)
                    <div class="mt-6 text-center">
                        <button
                            wire:click="loadMore"
                            wire:loading.attr="disabled"
                            class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <span wire:loading.remove>Cargar más tiendas</span>
                            <span wire:loading>Cargando...</span>
                        </button>
                    </div>
                @endif
            @else
                <div class="text-center py-12">
                    <p class="text-gray-500 text-lg">No se encontraron tiendas que coincidan con tu búsqueda.</p>
                </div>
            @endif
        </div>
    </main>
    <livewire:footer-menu />
</div>
