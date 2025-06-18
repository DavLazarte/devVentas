<div>
    <!-- Bottom Navigation -->
    <nav class="fixed bottom-0 bg-white border-t border-gray-200 py-2 z-10 w-full">
        <div class="flex justify-around items-center">
            <!-- Home -->
            <a href="/" class="flex flex-col items-center transition-all {{ request()->is('/') ? 'text-purple-600' : 'text-gray-500 hover:text-blue-500' }}">
                <div class="p-1 rounded-md {{ request()->is('/') ? 'bg-purple-100 text-purple-600' : 'text-gray-500' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                </div>
                <span class="text-xs mt-0.5 {{ request()->is('/') ? 'text-purple-600 font-medium' : 'text-gray-500' }}">Inicio</span>
            </a>

            <!-- Favorites -->
            <a href="/all-shops" class="flex flex-col items-center transition-all {{ request()->is('all-shops') ? 'text-purple-600' : 'text-gray-500 hover:text-blue-500' }}">
                <div class="p-1 rounded-md {{ request()->is('all-shops') ? 'bg-purple-100 text-purple-600' : 'text-gray-500' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                    </svg>
                </div>
                <span class="text-xs mt-0.5 {{ request()->is('all-shops') ? 'text-purple-600 font-medium' : 'text-gray-500' }}">Tiendas</span>
            </a>

            <!-- Map -->
            {{-- <div class="flex flex-col items-center">
                <div class="p-1 rounded-md text-gray-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <span class="text-xs mt-0.5 text-gray-500">Mapa</span>
            </div> --}}

            <!-- Cart -->
            <button wire:click="toggleCart" class="flex flex-col items-center">
                <div class="p-1 rounded-md text-gray-500 relative">
                    @if(count($items) > 0)
                        <span class="absolute -top-1 -right-1 bg-red-500 text-white text-[8px] px-1 rounded-full">{{ count($items) }}</span>
                    @endif
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <span class="text-xs mt-0.5 text-gray-500">Carrito</span>
            </button>

            <!-- Profile -->
            <div class="flex flex-col items-center opacity-50 cursor-not-allowed">
                <div class="p-1 rounded-md text-gray-500 relative">
                    <span class="absolute -top-1 -right-1 bg-red-500 text-white text-[8px] px-1 rounded-full">Próx</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
                <span class="text-xs mt-0.5 text-gray-500">Perfil</span>
            </div>
        </div>
    </nav>

    <!-- Cart Drawer -->
    <div class="fixed inset-0 z-50 overflow-hidden {{ $showCart ? '' : 'hidden' }}">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black bg-opacity-50" wire:click="toggleCart"></div>

        <!-- Drawer -->
        <div class="absolute inset-y-0 right-0 w-full sm:w-96 bg-white shadow-xl transform transition-transform duration-300 ease-in-out {{ $showCart ? 'translate-x-0' : 'translate-x-full' }}">

            <!-- Header -->
            <div class="sticky top-0 bg-white border-b border-gray-200 p-4 flex justify-between items-center">
                <h2 class="text-lg font-semibold text-gray-900">Carrito de Compras</h2>
                <button wire:click="toggleCart" class="text-gray-500 hover:text-gray-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Cart Content -->
            <div class="p-4">
                <livewire:cart />
            </div>

            <!-- Footer -->
            <div class="sticky bottom-0 bg-white border-t border-gray-200 p-4">
                <button
                    wire:click="$emit('proceedToCheckout')"
                    class="w-full bg-purple-600 text-white py-3 px-4 rounded-lg flex items-center justify-center space-x-2 hover:bg-purple-700 transition-colors {{ count($items) === 0 ? 'opacity-50 cursor-not-allowed' : '' }}"
                    {{ count($items) === 0 ? 'disabled' : '' }}
                >
                    <span>Finalizar Pedido</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>
