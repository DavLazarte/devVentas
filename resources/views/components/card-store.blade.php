@props(['store'])

<a href="{{ route('store.show', $store->slug) }}" class="block bg-white rounded-lg p-4 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
    <div class="flex space-x-3">
        <!-- Shop Logo -->
        <div class="flex-shrink-0">
            <div class="w-16 h-16 rounded-full overflow-hidden border border-gray-200">
                <img src="{{ $store->foto_logo ? Storage::url($store->foto_logo) : asset('images/default-logo.avif') }}"
                     alt="{{ $store->nombre }}"
                     class="w-full h-full object-cover">
            </div>
        </div>

        <!-- Shop Info -->
        <div class="flex-1 min-w-0">
            <!-- Header Row -->
            <div class="flex items-start justify-between mb-1">
                <div class="flex-1 min-w-0">
                    <h3 class="font-semibold text-gray-900 truncate">{{ $store->nombre }}</h3>
                    <div class="flex items-center space-x-2 mt-0.5">
                        @foreach($store->categories as $category)
                            <span class="bg-purple-100 text-purple-700 px-2 py-0.5 rounded text-xs font-medium">
                                {{ $category->name }}
                            </span>
                        @endforeach
                        @if($store->destacado)
                            <span class="bg-blue-100 text-blue-700 px-2 py-0.5 rounded text-xs font-medium flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Verificado
                            </span>
                        @endif
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    @if($store->rating_promedio)
                        <div class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                            <span class="text-sm font-medium text-gray-900 ml-1">{{ number_format($store->rating_promedio, 1) }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Description -->
            <p class="text-sm text-gray-600 mb-2">{{ Str::limit($store->descripcion, 100) }}</p>

            <!-- Footer -->
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-500">{{ $store->direccion }}</span>
            </div>
        </div>
    </div>
</a>
