@props(['subcategories', 'selectedSubcategory'])

<div class="bg-white py-3 px-4 mb-2 shadow-sm">
    <!-- Filter Chips -->
    <div class="flex overflow-x-auto space-x-2 pb-1 hide-scrollbar mb-3">
        <!-- Todas las categorías -->
        <button 
            wire:click="$emit('filterBySubcategory', null)"
            class="flex-shrink-0 px-4 py-2 rounded-full text-sm font-medium border transition-colors
                   {{ $selectedSubcategory === null ? 'bg-purple-600 text-white border-purple-600' : 'bg-white text-gray-700 border-gray-300 hover:border-purple-300' }}">
            Todas
        </button>

        @foreach($subcategories as $subcategory)
            <button 
                wire:click="$emit('filterBySubcategory', {{ $subcategory->id }})"
                class="flex-shrink-0 px-4 py-2 rounded-full text-sm font-medium border transition-colors
                       {{ $selectedSubcategory == $subcategory->id ? 'bg-purple-600 text-white border-purple-600' : 'bg-white text-gray-700 border-gray-300 hover:border-purple-300' }}">
                {{ $subcategory->name }}
            </button>
        @endforeach
    </div>

    <!-- Additional Filters para mas adelante-->
    {{-- <div class="flex space-x-2">
        <!-- Rating Filter -->
        <button class="flex items-center space-x-1 px-3 py-1.5 rounded-full border border-gray-300 text-sm text-gray-600 hover:border-purple-300">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
            </svg>
            <span>4.0+</span>
        </button>

        <!-- Distance Filter -->
        <button class="flex items-center space-x-1 px-3 py-1.5 rounded-full border border-gray-300 text-sm text-gray-600 hover:border-purple-300">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
            </svg>
            <span>Cerca</span>
        </button>

        <!-- Open Now Filter -->
        <button class="flex items-center space-x-1 px-3 py-1.5 rounded-full border border-gray-300 text-sm text-gray-600 hover:border-purple-300">
            <div class="w-2 h-2 bg-green-500 rounded-full"></div>
            <span>Abierto</span>
        </button>
    </div> --}}
</div>

@push('styles')
<style>
    .hide-scrollbar::-webkit-scrollbar {
        display: none;
    }
    .hide-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
</style>
@endpush
