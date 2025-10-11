<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">



    <!-- Bootstrap (ahora está después de tu CSS) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <!-- Tu CSS (cárgalo antes para que Bootstrap lo sobrescriba en caso de conflicto) -->
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
    <!-- SweetAlert2 CSS (después de Bootstrap para evitar conflictos) -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>




    <!-- Livewire Styles (solo una vez) -->
    @livewireStyles

    <!-- Scripts personalizados -->
    <script src="{{ mix('js/app.js') }}"></script>

    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    {{-- estilo de las notificaciones es solo para mvp mover a resources cuando lo validemos en produ --}}
    <style>
        .order-notification-toast {
            position: fixed;
            bottom: -100px;
            left: 50%;
            transform: translateX(-50%);
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 16px 20px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            z-index: 9999;
            max-width: 90vw;
            width: 400px;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .order-notification-toast.show {
            bottom: 20px;
        }

        .order-notification-content {
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        .order-notification-icon {
            font-size: 24px;
            flex-shrink: 0;
            animation: bellRing 0.6s ease;
        }

        .order-notification-text {
            flex: 1;
            min-width: 0;
        }

        .order-notification-text strong {
            display: block;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .order-notification-text p {
            margin: 0;
            font-size: 14px;
            opacity: 0.95;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .order-notification-text small {
            display: block;
            font-size: 12px;
            opacity: 0.8;
            margin-top: 4px;
        }

        @keyframes bellRing {

            0%,
            100% {
                transform: rotate(0deg);
            }

            10%,
            30% {
                transform: rotate(15deg);
            }

            20%,
            40% {
                transform: rotate(-15deg);
            }

            50% {
                transform: rotate(0deg);
            }
        }

        .notification-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #ef4444;
            color: white;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: none;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
            box-shadow: 0 2px 8px rgba(239, 68, 68, 0.4);
            animation: badgePop 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        @keyframes badgePop {
            from {
                transform: scale(0.5);
                opacity: 0;
            }

            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        [data-notification-bell].ring {
            animation: ringBell 0.6s ease;
        }

        @keyframes ringBell {

            0%,
            100% {
                transform: rotate(0deg);
            }

            15%,
            45% {
                transform: rotate(15deg);
            }

            30%,
            60% {
                transform: rotate(-15deg);
            }
        }

        @media (max-width: 640px) {
            .order-notification-toast {
                width: calc(100vw - 40px);
                max-width: none;
            }

            .order-notification-toast.show {
                bottom: 20px;
                left: 20px;
                right: 20px;
                transform: none;
            }
        }
    </style>

</head>

<body>
    <div>
        <script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v3.x.x/dist/alpine.min.js" defer></script>

        <div x-data="{ sidebarOpen: false }" class="flex h-screen bg-gray-200">
            <div :class="sidebarOpen ? 'block' : 'hidden'" @click="sidebarOpen = false"
                class="fixed inset-0 z-20 transition-opacity bg-black opacity-50 lg:hidden"></div>

            <div :class="sidebarOpen ? 'translate-x-0 ease-out' : '-translate-x-full ease-in'"
                class="fixed inset-y-0 left-0 z-30 w-64 overflow-y-auto transition duration-300 transform bg-gray-900 lg:translate-x-0 lg:static lg:inset-0">
                <div class="flex items-center justify-center mt-8">
                    <div class="flex items-center">
                       
                        <a href="https://ventas.tiendadux.ar/" class="mx-2 text-2xl font-semibold text-white">
                            <div class="flex items-center">
                                <img src="{{ asset('images/logodux.svg') }}" alt="Tienda Dux"
                                    class="h-6 w-auto sm:h-8 md:h-10 transition-all duration-200">
                            </div>
                        </a>
                    </div>
                </div>

                <nav class="mt-10">
                    <a class="flex items-center px-6 py-2 mt-4   text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 transition-colors rounded-md   hover:bg-purple-100 dark:hover:bg-purple-600"
                        href="{{ url('admin/sistema') }}">
                        <span class="ml-2 text-m"> 🏠 Inicio</span>
                    </a>
                    <a class="flex items-center px-6 py-2 mt-4  text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 transition-colors rounded-md   hover:bg-purple-100 dark:hover:bg-purple-600"
                        href="{{ url('admin/personas') }}">
                        <span class="ml-2 text-m"> 👥 Gestión de Personas</span>
                    </a>

                    <div x-data="{ isActive: false, open: false }">
                        <a href="#" @click="$event.preventDefault(); open = !open"
                            class="flex items-center px-6 py-2 mt-4  text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 transition-colors rounded-md   hover:bg-purple-100 dark:hover:bg-purple-600"
                            :class="{ 'bg-purple-100 dark:bg-purple-600': isActive || open }" role="button"
                            aria-haspopup="true" :aria-expanded="(open || isActive) ? 'true' : 'false'">
                            <span class="ml-2 text-m">
                                @if (auth()->user()->local->tipo == 'venta')
                                    📦 Depósito
                                @else
                                    🛠️ Gestión
                                @endif
                            </span>
                            <span class="ml-auto" aria-hidden="true">
                                <!-- active class 'rotate-180' -->
                                <svg class="w-4 h-4 transition-transform transform" :class="{ 'rotate-180': open }"
                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </span>

                            {{-- </span> --}}
                        </a>
                        <div role="menu" x-show="open" class="mt-2 space-y-2 px-7"
                            aria-label="{{ auth()->user()->local->tipo == 'venta' ? 'Depósito' : 'Gestión' }}">
                            <a class="flex items-center px-6 py-2 mt-4 text-gray-500 hover:bg-gray-700 hover:bg-opacity-25 hover:text-gray-100"
                                href="{{ url('admin/categorias') }}">
                                <span class="ml-2 text-m"> 📌 Categorías</span>
                            </a>
                            @if (auth()->user()->local->tipo == 'venta')
                                <a class="flex items-center px-6 py-2 mt-4 text-gray-500 hover:bg-gray-700 hover:bg-opacity-25 hover:text-gray-100"
                                    href="{{ url('admin/articulos') }}">
                                    <span class="ml-2 text-m"> 📦 Productos</span>
                                </a>
                            @else
                                <a class="flex items-center px-6 py-2 mt-4 text-gray-500 hover:bg-gray-700 hover:bg-opacity-25 hover:text-gray-100"
                                    href="{{ url('admin/servicios') }}">
                                    <span class="ml-2 text-m"> 🛅 Servicios</span>
                                </a>
                            @endif
                        </div>
                    </div>
                    <a class="flex items-center px-6 py-2 mt-4  text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 transition-colors rounded-md   hover:bg-purple-100 dark:hover:bg-purple-600"
                        href="{{ url('admin/pedidos') }}">
                        <span class="ml-2 text-m">
                            @if (auth()->user()->local->tipo == 'venta')
                                📋 Pedidos
                            @else
                                📅 Reservas
                            @endif
                        </span>
                    </a>
                    <div x-data="{ isActive: false, open: false }">
                        <a href="#" @click="$event.preventDefault(); open = !open"
                            class="flex items-center px-6 py-2 mt-4  text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 transition-colors rounded-md   hover:bg-purple-100 dark:hover:bg-purple-600"
                            :class="{ 'bg-purple-100 dark:bg-purple-600': isActive || open }" role="button"
                            aria-haspopup="true" :aria-expanded="(open || isActive) ? 'true' : 'false'">
                            <span class="ml-2 text-m">
                                @if (auth()->user()->local->tipo == 'venta')
                                    🛒 Ventas
                                @else
                                    🛠️ Servicios
                                @endif
                            </span>
                            <span class="ml-auto" aria-hidden="true">
                                <!-- active class 'rotate-180' -->
                                <svg class="w-4 h-4 transition-transform transform" :class="{ 'rotate-180': open }"
                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </span>
                        </a>
                        <div role="menu" x-show="open" class="mt-2 space-y-2 px-7"
                            aria-label="{{ auth()->user()->local->tipo == 'venta' ? 'Ventas' : 'Servicios' }}">
                            @if (auth()->user()->local->tipo == 'venta')
                                <a class="flex items-center px-6 py-2 mt-4 text-gray-500 hover:bg-gray-700 hover:bg-opacity-25 hover:text-gray-100"
                                    href="{{ url('admin/ventas') }}">
                                    <span class="ml-2 text-m"> 🛍 Punto de Venta</span>
                                </a>
                                <a class="flex items-center px-6 py-2 mt-4 text-gray-500 hover:bg-gray-700 hover:bg-opacity-25 hover:text-gray-100"
                                    href="{{ url('admin/list-ventas') }}">
                                    <span class="ml-2 text-m"> 📊 Ver Ventas</span>
                                </a>
                            @else
                                <a class="flex items-center px-6 py-2 mt-4 text-gray-500 hover:bg-gray-700 hover:bg-opacity-25 hover:text-gray-100"
                                    href="#">
                                    <span class="ml-2 text-m"> 📊 Muy Pronto</span>
                                </a>
                            @endif
                        </div>

                    </div>

                    <div x-data="{ isActive: false, open: false }">
                        <a href="#" @click="$event.preventDefault(); open = !open"
                            class="flex items-center px-6 py-2 mt-4 text-gray-400   transition-colors rounded-md   hover:bg-purple-100 dark:hover:bg-purple-600 hover:text-gray-900 dark:hover:text-gray-100"
                            :class="{ 'bg-purple-100 dark:bg-purple-600': isActive || open }" role="button"
                            aria-haspopup="true" :aria-expanded="(open || isActive) ? 'true' : 'false'">
                            <span class="ml-2 text-m"> 📑 Compras </span>
                            <span class="ml-auto" aria-hidden="true">
                                <!-- active class 'rotate-180' -->
                                <svg class="w-4 h-4 transition-transform transform" :class="{ 'rotate-180': open }"
                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </span>
                        </a>
                        <div role="menu" x-show="open" class="mt-2 space-y-2 px-7" aria-label="Compras">
                            @if (auth()->user()->local->tipo == 'venta')
                                <a class="flex items-center px-6 py-2 mt-4 text-gray-500 hover:bg-gray-700 hover:bg-opacity-25 hover:text-gray-100"
                                    href="{{ url('admin/compras') }}">
                                    <span class="ml-2 text-m">📥 Cargar Compras</span>
                                </a>
                                <a class="flex items-center px-6 py-2 mt-4 text-gray-500 hover:bg-gray-700 hover:bg-opacity-25 hover:text-gray-100"
                                    href="{{ url('admin/list-compras') }}">
                                    <span class="ml-2 text-m"> 📊 Ver Compras</span>
                                </a>
                            @else
                                <a class="flex items-center px-6 py-2 mt-4 text-gray-500 hover:bg-gray-700 hover:bg-opacity-25 hover:text-gray-100"
                                    href="#">
                                    <span class="ml-2 text-m"> 📊 Muy Pronto</span>
                                </a>
                            @endif
                        </div>
                    </div>
                    <div x-data="{ isActive: false, open: false }">
                        <a href="#" @click="$event.preventDefault(); open = !open"
                            class="flex items-center px-6 py-2 mt-4  text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 transition-colors rounded-md   hover:bg-purple-100 dark:hover:bg-purple-600"
                            :class="{ 'bg-purple-100 dark:bg-purple-600': isActive || open }" role="button"
                            aria-haspopup="true" :aria-expanded="(open || isActive) ? 'true' : 'false'">
                            <span class="ml-2 text-m"> 💰 Finanzas </span>
                            <span class="ml-auto" aria-hidden="true">
                                <!-- active class 'rotate-180' -->
                                <svg class="w-4 h-4 transition-transform transform" :class="{ 'rotate-180': open }"
                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </span>
                        </a>
                        <div role="menu" x-show="open" class="mt-2 space-y-2 px-7" aria-label="Finanzas">
                            @if (auth()->user()->local->tipo == 'venta')
                                <a class="flex items-center px-6 py-2 mt-4 text-gray-500 hover:bg-gray-700 hover:bg-opacity-25 hover:text-gray-100"
                                    href="{{ url('admin/ventas-saldos') }}">
                                    <span class="ml-2 text-m">📋 Ventas con Saldos</span>
                                </a>
                                <a class="flex items-center px-6 py-2 mt-4 text-gray-500 hover:bg-gray-700 hover:bg-opacity-25 hover:text-gray-100"
                                    href="{{ url('admin/ingresos') }}">
                                    <span class="ml-2 text-m">🏦 Listados de Pagos </span>
                                </a>
                                <a class="flex items-center px-6 py-2 mt-4 text-gray-500 hover:bg-gray-700 hover:bg-opacity-25 hover:text-gray-100"
                                    href="{{ url('admin/salidas') }}">


                                    <span class="ml-2 text-m">📉 Gastos</span>
                                </a>
                                <a class="flex items-center px-6 py-2 mt-4 text-gray-500 hover:bg-gray-700 hover:bg-opacity-25 hover:text-gray-100"
                                    href="{{ url('admin/caja') }}">
                                    <span class="ml-2 text-m">💵 Ver Caja </span>
                                </a>
                            @else
                                <a class="flex items-center px-6 py-2 mt-4 text-gray-500 hover:bg-gray-700 hover:bg-opacity-25 hover:text-gray-100"
                                    href="#">
                                    <span class="ml-2 text-m"> 📊 Muy Pronto</span>
                                </a>
                            @endif
                        </div>
                    </div>
                    {{-- </div> --}}

                </nav>

            </div>
            <div class="flex flex-col flex-1 overflow-hidden">
                <header class="flex items-center justify-between px-6 py-4 bg-white border-b-4 border-purple-600">
                    <div class="flex items-center">
                        <button @click="sidebarOpen = true" class="text-gray-500 focus:outline-none lg:hidden">
                            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path d="M4 6H20M4 12H20M4 18H11" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round"></path>
                            </svg>
                        </button>

                        <div class="relative mx-4 lg:mx-0">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                                <svg class="w-5 h-5 text-gray-500" viewBox="0 0 24 24" fill="none">
                                    <path
                                        d="M21 21L15 15M17 10C17 13.866 13.866 17 10 17C6.13401 17 3 13.866 3 10C3 6.13401 6.13401 3 10 3C13.866 3 17 6.13401 17 10Z"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                    </path>
                                </svg>
                            </span>
                        </div>
                    </div>

                    <div class="flex items-center">
                        <div class="relative">
                            <button data-notification-bell class="flex mx-4 text-gray-600 focus:outline-none relative">
                                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M15 17H20L18.5951 15.5951C18.2141 15.2141 18 14.6973 18 14.1585V11C18 8.38757 16.3304 6.16509 14 5.34142V5C14 3.89543 13.1046 3 12 3C10.8954 3 10 3.89543 10 5V5.34142C7.66962 6.16509 6 8.38757 6 11V14.1585C6 14.6973 5.78595 15.2141 5.40493 15.5951L4 17H9M15 17V18C15 19.6569 13.6569 21 12 21C10.3431 21 9 19.6569 9 18V17M15 17H9"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                    </path>
                                </svg>
                            </button>
                        </div>

                        <div class="hidden sm:flex sm:items-center sm:ml-6">
                            <x-dropdown align="right" width="48">
                                <x-slot name="trigger">
                                    <button
                                        class="flex items-center text-m font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out">
                                        <div>{{ Auth::user()->name }}</div>

                                        <div class="ml-1">
                                            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                                viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    </button>
                                </x-slot>

                                <x-slot name="content">
                                    <!-- Authentication -->
                                    <form method="POST" action="{{ route('voyager.logout') }}">
                                        @csrf

                                        <x-dropdown-link :href="route('voyager.logout')"
                                            onclick="event.preventDefault();
                                                     this.closest('form').submit();">
                                            {{ __('Salir') }}
                                        </x-dropdown-link>
                                    </form>
                                </x-slot>

                            </x-dropdown>
                        </div>
                    </div>

                </header>
                <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-200">
                    @if (auth()->user()->local->tipo == 'venta')
                        <div class="max-w-xl mx-auto mt-2 px-1 sm:px-1 lg:px-1">
                            @include('livewire.accesos-directos')
                        </div>
                    @endif
                    <div class="font-sans text-gray-900 antialiased">
                        {{ $slot }}
                    </div>
                </main>
            </div>
        </div>
    </div>
    @stack('modals')
    @livewireScripts
    @stack('js')

    <script src="{{ asset('/js/jquery.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.19/index.global.min.js"></script>

    {{-- <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script> --}}
    <script>
        document.addEventListener('keydown', function(event) {
            if (event.key === "F9") { // Puedes cambiar esto a cualquier otra tecla
                event.preventDefault(); // Previene la funcionalidad predeterminada de la tecla
                document.getElementById('saveSaleButton').click(); // Simula un click en el botón de guardar
            }
        });
    </script>
    <script>
        class OrderNotificationPoller {
            constructor(options = {}) {
                this.pollingInterval = options.pollingInterval || 60000;
                this.apiEndpoint = options.apiEndpoint || '/admin/api/check-new-orders';
                this.bellElement = options.bellElement || document.querySelector('[data-notification-bell]');
                this.soundEnabled = options.soundEnabled !== false;
                this.pollingTimerId = null;
                this.pendingNotifications = new Set();
                this.notificationStore = null;

                this.init();
            }

            init() {
                if (this.isUserAuthenticated()) {
                    this.startPolling();
                    console.log('Polling de pedidos iniciado - Revisar cada ' + (this.pollingInterval / 1000) + 's');
                }
            }

            isUserAuthenticated() {
                return !!document.querySelector('meta[name="csrf-token"]');
            }

            setNotificationStore(store) {
                this.notificationStore = store;
            }

            startPolling() {
                this.checkOrders();
                this.pollingTimerId = setInterval(() => this.checkOrders(), this.pollingInterval);
            }

            stopPolling() {
                if (this.pollingTimerId) {
                    clearInterval(this.pollingTimerId);
                    this.pollingTimerId = null;
                    console.log('Polling detenido');
                }
            }

            async checkOrders() {
                try {
                    const response = await fetch(this.apiEndpoint, {
                        method: 'GET',
                        credentials: 'include',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                        }
                    });

                    if (!response.ok) {
                        if (response.status === 401) {
                            this.stopPolling();
                            console.log('Sesión expirada, polling detenido');
                            return;
                        }
                        throw new Error('Error en la respuesta del servidor');
                    }

                    const data = await response.json();

                    if (data.hasNewOrders && data.orders) {
                        this.handleNewOrders(data.orders);
                    }
                } catch (error) {
                    console.error('Error al verificar pedidos:', error);
                }
            }

            handleNewOrders(orders) {
                orders.forEach(order => {
                    if (!this.pendingNotifications.has(order.id)) {
                        this.pendingNotifications.add(order.id);

                        // Mostrar el toast
                        this.showNotification(order);

                        // Agregar al store de Alpine
                        if (this.notificationStore) {
                            this.notificationStore.push(order);
                            console.log('Notificación agregada al store:', order);
                        }

                        if (this.soundEnabled) {
                            this.playSound();
                        }

                        this.updateBell(this.pendingNotifications.size);
                    }
                });
            }

            showNotification(order) {
                const toast = document.createElement('div');
                toast.className = 'order-notification-toast';
                toast.innerHTML = `
            <div class="order-notification-content">
                <div class="order-notification-icon">🔔</div>
                <div class="order-notification-text">
                    <strong>Nuevo Pedido #${order.id}</strong>
                    <p>${order.cliente} - $${parseFloat(order.total).toFixed(2)}</p>
                    <small>${order.created_at}</small>
                </div>
            </div>
        `;

                document.body.appendChild(toast);
                setTimeout(() => toast.classList.add('show'), 10);

                setTimeout(() => {
                    toast.classList.remove('show');
                    setTimeout(() => toast.remove(), 300);
                }, 5000);

                toast.addEventListener('click', () => {
                    window.location.href = '/admin/pedidos';
                });
            }

            updateBell(count) {
                // No mostrar badge si estamos en la página de pedidos
                if (window.location.pathname === '/admin/pedidos') {
                    return;
                }

                if (this.bellElement) {
                    let badge = this.bellElement.querySelector('.notification-badge');
                    if (!badge) {
                        badge = document.createElement('div');
                        badge.className = 'notification-badge';
                        this.bellElement.appendChild(badge);
                    }

                    if (count > 0) {
                        badge.innerHTML = `
                            <div class="absolute -top-2 -right-2 flex items-center gap-2 bg-red-500 text-white px-3 py-1 rounded-full text-xs font-semibold cursor-pointer hover:bg-red-600 transition shadow-lg whitespace-nowrap">
                                ${count} nuevo${count > 1 ? 's' : ''} pedido${count > 1 ? 's' : ''}
                                <span>→</span>
                            </div>
                        `;
                        badge.style.display = 'block';
                        badge.onclick = (e) => {
                            e.stopPropagation();
                            window.location.href = '/admin/pedidos';
                        };
                    } else {
                        badge.style.display = 'none';
                    }

                    this.bellElement.classList.add('ring');
                    setTimeout(() => this.bellElement.classList.remove('ring'), 600);
                }
            }

            playSound() {
                try {
                    const audioContext = new(window.AudioContext || window.webkitAudioContext)();
                    const oscillator = audioContext.createOscillator();
                    const gainNode = audioContext.createGain();

                    oscillator.connect(gainNode);
                    gainNode.connect(audioContext.destination);

                    oscillator.frequency.value = 800;
                    oscillator.type = 'sine';
                    gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
                    gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.5);

                    oscillator.start(audioContext.currentTime);
                    oscillator.stop(audioContext.currentTime + 0.5);
                } catch (error) {
                    console.log('No se pudo reproducir sonido:', error);
                }
            }

            clearNotifications() {
                this.pendingNotifications.clear();
                if (this.notificationStore) {
                    this.notificationStore.length = 0;
                }
                this.updateBell(0);
            }
        }

        // Inicializar cuando el DOM esté listo
        document.addEventListener('DOMContentLoaded', () => {
            const bellButton = document.querySelector('[data-notification-bell]');

            window.orderPoller = new OrderNotificationPoller({
                pollingInterval: 60000,
                apiEndpoint: '/admin/api/check-new-orders',
                bellElement: bellButton,
                soundEnabled: true
            });

            // Esperar a que Alpine esté disponible y conectar el store
            const checkAlpine = setInterval(() => {
                const notificationDiv = document.querySelector('[x-data*="notifications"]');
                if (notificationDiv && notificationDiv.__x) {
                    clearInterval(checkAlpine);
                    window.orderPoller.setNotificationStore(notificationDiv.__x.$data.notifications);
                    console.log('Store de notificaciones conectado');
                }
            }, 100);

            setTimeout(() => clearInterval(checkAlpine), 5000);
        });

        // Pausar polling si el usuario se va de la página
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                window.orderPoller?.stopPolling();
            } else {
                window.orderPoller?.startPolling();
            }
        });
    </script>


</body>

</html>
