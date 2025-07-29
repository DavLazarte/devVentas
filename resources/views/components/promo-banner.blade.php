<!-- Promotional Slider -->
<div class="relative mx-4 mb-6 mt-4">
    <div class="swiper promoSwiper rounded-2xl overflow-hidden shadow-lg">
        <div class="swiper-wrapper">
            <!-- Slide 1 - Banner actual mejorado -->
            <div class="swiper-slide">
                <div class="relative bg-gradient-to-r from-purple-600 to-purple-700 text-white p-6 min-h-[140px] flex items-center">
                    <div class="flex-1">
                        <h2 class="text-xl font-bold mb-1">¡Conectá con las oportunidades!</h2>
                        <p class="text-sm opacity-90 mb-3">Sumá tu negocio o servicio gratis</p>
                        <a href="https://walink.co/74faf1" target="_blank" rel="noopener noreferrer"
                           class="inline-flex items-center bg-white text-purple-600 px-4 py-2 rounded-full text-sm font-medium hover:bg-gray-100 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                            </svg>
                            Más info
                        </a>
                    </div>
                    <!-- Decoración visual -->
                    <div class="absolute right-4 top-4 w-16 h-16 bg-white/10 rounded-full flex items-center justify-center">
                        <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Slide 2 - Ofertas especiales -->
            <div class="swiper-slide">
                <div class="relative bg-gradient-to-r from-green-500 to-green-600 text-white p-6 min-h-[140px] flex items-center">
                    <div class="flex-1">
                        <h2 class="text-xl font-bold mb-1">🔥 Ofertas Especiales</h2>
                        <p class="text-sm opacity-90 mb-3">Hasta 50% OFF en productos seleccionados</p>
                        <button class="inline-flex items-center bg-white text-green-600 px-4 py-2 rounded-full text-sm font-medium hover:bg-gray-100 transition-colors">
                            Ver ofertas
                        </button>
                    </div>
                    <div class="absolute right-4 top-4 w-16 h-16 bg-white/10 rounded-full flex items-center justify-center">
                        <span class="text-2xl">💝</span>
                    </div>
                </div>
            </div>

            <!-- Slide 3 - Envío gratis -->
            <div class="swiper-slide">
                <div class="relative bg-gradient-to-r from-blue-500 to-blue-600 text-white p-6 min-h-[140px] flex items-center">
                    <div class="flex-1">
                        <h2 class="text-xl font-bold mb-1">🚚 Envío Gratis</h2>
                        <p class="text-sm opacity-90 mb-3">En compras mayores a $5000</p>
                        <button class="inline-flex items-center bg-white text-blue-600 px-4 py-2 rounded-full text-sm font-medium hover:bg-gray-100 transition-colors">
                            Comprar ahora
                        </button>
                    </div>
                    <div class="absolute right-4 top-4 w-16 h-16 bg-white/10 rounded-full flex items-center justify-center">
                        <span class="text-2xl">🛒</span>
                    </div>
                </div>
            </div>

            <!-- Slide 4 - Nuevos productos -->
            <div class="swiper-slide">
                <div class="relative bg-gradient-to-r from-orange-500 to-orange-600 text-white p-6 min-h-[140px] flex items-center">
                    <div class="flex-1">
                        <h2 class="text-xl font-bold mb-1">✨ Nuevos Productos</h2>
                        <p class="text-sm opacity-90 mb-3">Descubrí las últimas novedades</p>
                        <button class="inline-flex items-center bg-white text-orange-600 px-4 py-2 rounded-full text-sm font-medium hover:bg-gray-100 transition-colors">
                            Explorar
                        </button>
                    </div>
                    <div class="absolute right-4 top-4 w-16 h-16 bg-white/10 rounded-full flex items-center justify-center">
                        <span class="text-2xl">🌟</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Pagination dots -->
        <div class="swiper-pagination !bottom-3"></div>
    </div>
</div>

<!-- Swiper CSS y JS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css"/>
<script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    new Swiper('.promoSwiper', {
        slidesPerView: 1,
        spaceBetween: 0,
        loop: true,
        autoplay: {
            delay: 4000,
            disableOnInteraction: false,
        },
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
        },
        effect: 'slide',
        speed: 600,
    });
});
</script>