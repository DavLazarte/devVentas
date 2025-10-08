<div>
    <!-- Header -->
    <header class="sticky top-0 z-10 bg-white shadow-sm">
        <div class="container mx-auto px-4 py-3 flex items-center justify-between">
            <!-- Back Button -->
            <button onclick="history.back()" class="p-2 -ml-2 rounded-full hover:bg-gray-100">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-600" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </button>

            <!-- Title -->
            <h1 class="text-lg font-semibold text-gray-900 truncate mx-4">{{ $local->nombre }}</h1>

            <!-- Actions -->
            <div class="flex items-center space-x-2">
                <!-- Share Button -->
                <button onclick="shareContent()" class="p-2 rounded-full hover:bg-gray-100">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-600" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z" />
                    </svg>
                </button>

                <!-- Favorite Button -->
                {{-- <button wire:click="toggleFavorite" class="p-2 rounded-full hover:bg-gray-100">
                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="h-6 w-6 {{ $isFavorite ? 'text-red-500 fill-current' : 'text-gray-600' }}" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                    </svg>
                </button> --}}
            </div>
        </div>
    </header>

    <main class="pb-20">
        <!-- Cover Photo and Logo -->
        <div class="relative">
            <!-- Cover Photo -->
            <div class="h-32 bg-gradient-to-r from-purple-400 to-purple-600 relative overflow-hidden">
                @if ($local->foto_portada)
                    <img src="{{ asset('storage/' . $local->foto_portada) }}" alt="{{ $local->nombre }} cover"
                        class="w-full h-full object-cover">
                @endif

                <!-- Status Badge -->
                <div class="absolute top-4 right-4">
                    <span
                        class="bg-{{ $local->estado === 'activo' ? 'green' : 'red' }}-500 text-white px-3 py-1 rounded-full text-sm font-medium">
                        {{ $local->estado === 'activo' ? 'Abierto' : 'Cerrado' }}
                    </span>
                </div>
            </div>

            <!-- Logo -->
            <div class="absolute -bottom-6 left-4">
                <div class="w-12 h-12 rounded-full border-4 border-white bg-white overflow-hidden shadow-lg">
                    <img src="{{ asset('storage/' . $local->foto_logo) }}" alt="{{ $local->nombre }} logo"
                        class="w-full h-full object-cover">
                </div>
            </div>
        </div>

        <!-- Shop Info -->
        <div class="bg-white px-4 pt-8 pb-4 mb-2">
            <!-- Name and Type -->
            <div class="mb-2">
                <h1 class="text-xl font-bold text-gray-900 mb-1">{{ $local->nombre }}</h1>
                <div class="flex items-center space-x-2">
                    <span class="bg-purple-100 text-purple-700 px-2 py-0.5 rounded-full text-xs font-medium">
                        {{ $local->tipo }}
                    </span>
                    @if ($local->plan === 'premium')
                        <span
                            class="bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full text-xs font-medium flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-0.5" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Verificado
                        </span>
                    @endif
                </div>
            </div>
            <!-- Description and Contact Info -->
            <div class="mt-4">
                <div class="bg-white rounded-lg">
                    <h2 class="text-lg font-semibold text-gray-900 mb-3">Acerca de</h2>
                    <p class="text-gray-700 text-sm leading-relaxed mb-4">{{ $local->descripcion }}</p>

                    <div class="grid grid-cols-2 gap-4">
                        <!-- Columna Izquierda -->
                        <div class="space-y-3">
                            @if ($local->direccion)
                                <div class="flex items-start space-x-2">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="h-4 w-4 text-gray-400 mt-0.5 flex-shrink-0" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <div>
                                        <p class="text-sm text-gray-900">{{ $local->direccion }}</p>
                                        <button class="text-purple-600 text-xs font-medium mt-0.5">Ver en mapa</button>
                                    </div>
                                </div>
                            @endif

                            @if ($local->telefono)
                                <div class="flex items-center space-x-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400 flex-shrink-0"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                    </svg>
                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $local->telefono) }}"
                                        target="_blank" class="text-sm text-gray-900 hover:text-purple-600">
                                        {{ $local->telefono }}
                                    </a>
                                </div>
                            @endif

                            @if ($local->email)
                                <div class="flex items-center space-x-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400 flex-shrink-0"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                    <a href="mailto:{{ $local->email }}"
                                        class="text-sm text-gray-900 hover:text-purple-600">{{ $local->email }}</a>
                                </div>
                            @endif
                        </div>

                        <!-- Columna Derecha -->
                        <div class="space-y-3">
                            @if ($local->sitio_web)
                                <div class="flex items-center space-x-2">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="h-4 w-4 text-gray-400 flex-shrink-0" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                                    </svg>
                                    <a href="{{ $local->sitio_web }}" target="_blank"
                                        class="text-sm text-gray-900 hover:text-purple-600 truncate">{{ $local->sitio_web }}</a>
                                </div>
                            @endif

                            @if ($local->redes_sociales)
                                @php
                                    $redes = json_decode($local->redes_sociales, true);
                                @endphp

                                @if (isset($redes['facebook']))
                                    <div class="flex items-center space-x-2">
                                        <svg class="h-4 w-4 text-gray-400 flex-shrink-0" fill="currentColor"
                                            viewBox="0 0 24 24">
                                            <path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z" />
                                        </svg>
                                        <a href="{{ $redes['facebook'] }}" target="_blank"
                                            class="text-sm text-gray-900 hover:text-purple-600">Facebook</a>
                                    </div>
                                @endif

                                @if (isset($redes['instagram']))
                                    <div class="flex items-center space-x-2">
                                        <svg class="h-4 w-4 text-gray-400 flex-shrink-0" fill="currentColor"
                                            viewBox="0 0 24 24">
                                            <path
                                                d="M12 2c2.717 0 3.056.01 4.122.06 1.065.05 1.79.217 2.428.465.66.254 1.216.598 1.772 1.153a4.908 4.908 0 011.153 1.772c.247.637.415 1.363.465 2.428.047 1.066.06 1.405.06 4.122 0 2.717-.01 3.056-.06 4.122-.05 1.065-.218 1.79-.465 2.428a4.883 4.883 0 01-1.153 1.772 4.915 4.915 0 01-1.772 1.153c-.637.247-1.363.415-2.428.465-1.066.047-1.405.06-4.122.06-2.717 0-3.056-.01-4.122-.06-1.065-.05-1.79-.218-2.428-.465a4.89 4.89 0 01-1.772-1.153 4.904 4.904 0 01-1.153-1.772c-.248-.637-.415-1.363-.465-2.428C2.013 15.056 2 14.717 2 12c0-2.717.01-3.056.06-4.122.05-1.066.217-1.79.465-2.428a4.88 4.88 0 011.153-1.772A4.897 4.897 0 015.45 2.525c.638-.248 1.362-.415 2.428-.465C8.944 2.013 9.283 2 12 2zm0 5a5 5 0 100 10 5 5 0 000-10zm6.5-.25a1.25 1.25 0 10-2.5 0 1.25 1.25 0 002.5 0zM12 9a3 3 0 110 6 3 3 0 010-6z" />
                                        </svg>
                                        <a href="{{ $redes['instagram'] }}" target="_blank"
                                            class="text-sm text-gray-900 hover:text-purple-600">Instagram</a>
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Products and Services Tabs -->
        <div class="bg-white px-4 py-3 mb-2">
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8">
                    <button wire:click="setActiveTab('productos')"
                        class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'productos' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        Productos
                    </button>
                    <button wire:click="setActiveTab('servicios')"
                        class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'servicios' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        Servicios
                    </button>
                    <button wire:click="setActiveTab('todo')"
                        class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'todo' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        Todo
                    </button>
                </nav>
            </div>
        </div>

        <!-- Products/Services Grid -->
        <div class="px-4">
            <div class="grid grid-cols-2 gap-3">
                @if ($activeTab === 'productos' || $activeTab === 'todo')
                    @foreach ($products as $product)
                        {{-- <x-product-card :product="$product" :showShop="false" /> --}}
                        <livewire:product-card :product="$product" :type="'articulo'" :showShop="true" :showFavorite="true"
                            :showAddButton="true" :wire:key="'product-'.$product->idarticulo" />
                    @endforeach
                @endif

                @if ($activeTab === 'servicios' || $activeTab === 'todo')
                    @foreach ($services as $service)
                        <livewire:product-card :product="$service" :type="'servicio'" :showShop="true" :showFavorite="true"
                            :showAddButton="true" :wire:key="'service-'.$service->idservicio" />
                    @endforeach
                @endif
            </div>
        </div>
        @if ($hasMorePages)
            <div class="mt-6 text-center">
                <button wire:click="loadMore" wire:loading.attr="disabled"
                    class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 disabled:opacity-50 disabled:cursor-not-allowed">
                    <span wire:loading.remove>Cargar más
                        {{ $activeTab === 'productos' ? 'productos' : ($activeTab === 'servicios' ? 'servicios' : 'elementos') }}</span>
                    <span wire:loading>Cargando...</span>
                </button>
            </div>
        @endif
    </main>
    <livewire:footer-menu />

</div>
<script>
    function shareContent() {
        console.log('Share clicked');
        if (navigator.share) {
            navigator.share({
                title: '{{ $local->nombre }}',
                text: '{{ $local->descripcion }}',
                url: window.location.href
            }).catch(() => copyLink());
        } else {
            copyLink();
        }
    }

    function copyLink() {
        const url = window.location.href;
        
        // Método 1: Clipboard API (recomendado)
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(url).then(() => {
                console.log('✓ Enlace copiado al portapapeles');
                showNotification('¡Enlace copiado!');
            }).catch(err => {
                console.error('Error al copiar:', err);
                fallbackCopy(url);
            });
        } else {
            // Método 2: Fallback para navegadores antiguos
            fallbackCopy(url);
        }
    }

    function fallbackCopy(text) {
        const textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.style.position = 'fixed';
        textarea.style.opacity = '0';
        document.body.appendChild(textarea);
        textarea.select();
        
        try {
            document.execCommand('copy');
            console.log('✓ Enlace copiado (método fallback)');
            showNotification('¡Enlace copiado!');
        } catch (err) {
            console.error('Error al copiar:', err);
        }
        
        document.body.removeChild(textarea);
    }

    function showNotification(message) {
        // Simple toast notification
        const toast = document.createElement('div');
        toast.textContent = message;
        toast.style.cssText = `
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: #10b981;
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 14px;
            z-index: 9999;
            animation: slideUp 0.3s ease;
        `;
        document.body.appendChild(toast);
        
        setTimeout(() => toast.remove(), 2000);
    }
</script>

<style>
    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateX(-50%) translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
        }
    }
</style>
