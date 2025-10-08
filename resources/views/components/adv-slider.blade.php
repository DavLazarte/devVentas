<!-- Advertising Banner Slider -->
<div class="relative mb-6">
    <div class="swiper advertisingSwiper overflow-hidden">
        <div class="swiper-wrapper">
            <!-- Ad Slide 1 -->
            <div class="swiper-slide">
                <a href="https://ventas.tiendadux.ar/store/diri-pintureria" class="block relative overflow-hidden rounded-lg">
                    <img src="{{ asset('images/banners/banner-1.png') }}" 
                         alt="Banner publicitario 1" 
                         class="w-full h-auto object-contain">
                    <button class="absolute bottom-4 right-4 bg-white text-gray-900 px-4 py-2 rounded-full text-sm font-semibold hover:bg-gray-100 transition-colors shadow-lg">
                        Ver tienda
                    </button>
                </a>
            </div>
        
            <!-- Ad Slide 2 -->
            <div class="swiper-slide">
                <a href="https://ventas.tiendadux.ar/store/alakia?activeTab=productos" class="block relative overflow-hidden rounded-lg">
                    <img src="{{ asset('images/banners/banner-2.png') }}" 
                         alt="Banner publicitario 2" 
                         class="w-full h-auto object-contain">
                    <button class="absolute bottom-4 right-4 bg-white text-gray-900 px-4 py-2 rounded-full text-sm font-semibold hover:bg-gray-100 transition-colors shadow-lg">
                        Ver tienda
                    </button>
                </a>
            </div>
        
            <!-- Ad Slide 3 -->
            <div class="swiper-slide">
                <a href="https://ventas.tiendadux.ar/store/skill-fitnnes?activeTab=productos" class="block relative overflow-hidden rounded-lg">
                    <img src="{{ asset('images/banners/banner-3.png') }}" 
                         alt="Banner publicitario 3" 
                         class="w-full h-auto object-contain">
                    <button class="absolute bottom-4 right-4 bg-white text-gray-900 px-4 py-2 rounded-full text-sm font-semibold hover:bg-gray-100 transition-colors shadow-lg">
                       Ver Tienda
                    </button>
                </a>
            </div>
        
            <!-- Ad Slide 4 -->
            <div class="swiper-slide">
                <a href="https://ventas.tiendadux.ar/store/lumiere?activeTab=servicios" class="block relative overflow-hidden rounded-lg">
                    <img src="{{ asset('images/banners/banner-4.png') }}" 
                         alt="Banner publicitario 4" 
                         class="w-full h-auto object-contain">
                    <button class="absolute bottom-4 right-4 bg-white text-gray-900 px-4 py-2 rounded-full text-sm font-semibold hover:bg-gray-100 transition-colors shadow-lg">
                       Ver Servicios
                    </button>
                </a>
            </div>
        </div>
        
        <!-- Navigation arrows -->
        <div class="swiper-button-next !text-white !w-10 !h-10 !mt-0 !top-1/2 !right-4 bg-black/30 !rounded-full hover:bg-black/50 transition-colors after:!text-sm"></div>
        <div class="swiper-button-prev !text-white !w-10 !h-10 !mt-0 !top-1/2 !left-4 bg-black/30 !rounded-full hover:bg-black/50 transition-colors after:!text-sm"></div>
        
        <!-- Pagination dots -->
        <div class="swiper-pagination !bottom-4"></div>
    </div>
</div>

<!-- Swiper CSS y JS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css"/>
<script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    new Swiper('.advertisingSwiper', {
        slidesPerView: 1,
        spaceBetween: 0,
        loop: true,
        autoplay: {
            delay: 5000, // 5 segundos por slide para dar tiempo a leer
            disableOnInteraction: false,
        },
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
            bulletActiveClass: 'swiper-pagination-bullet-active',
        },
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
        effect: 'slide',
        speed: 800,
        grabCursor: true,
        // Pausar autoplay al hover
        on: {
            init: function() {
                this.el.addEventListener('mouseenter', () => {
                    this.autoplay.stop();
                });
                this.el.addEventListener('mouseleave', () => {
                    this.autoplay.start();
                });
            }
        }
    });
});
</script>

<style>
/* Custom pagination bullets */
.advertisingSwiper .swiper-pagination-bullet {
    width: 12px;
    height: 12px;
    background: rgba(255, 255, 255, 0.5);
    opacity: 1;
}

.advertisingSwiper .swiper-pagination-bullet-active {
    background: white;
}

/* Smooth transitions for CTAs */
.advertisingSwiper a {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
</style>