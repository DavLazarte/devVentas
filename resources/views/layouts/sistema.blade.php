<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">

    <!-- Tu CSS (cárgalo antes para que Bootstrap lo sobrescriba en caso de conflicto) -->
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">

    <!-- Bootstrap (ahora está después de tu CSS) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
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
                        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"
                            xmlns:xlink="http://www.w3.org/1999/xlink" fill="#000000">
                            <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                            <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                            <g id="SVGRepo_iconCarrier">
                                <defs>
                                    <polygon id="cart-a" points="0 0 1 3 11 3 12 0"></polygon>
                                    <path id="cart-c"
                                        d="M4.01867032,7 L15.8260605,7 C15.8624695,7 15.8984188,7.0019917 15.9338076,7.00587329 L18.2075854,0.659227726 C18.3493483,0.263534454 18.7208253,0 19.1368299,0 L22.0108287,0 C22.5567095,0 22.9992334,0.44771525 22.9992334,1 C22.9992334,1.55228475 22.5567095,2 22.0108287,2 L19.8298966,2 L15.7669004,13.3407723 C15.6251376,13.7364655 15.2536606,14 14.8376559,14 L4.95360962,14 C4.52817021,14 4.15046241,13.7245699 4.01592666,13.3162278 L1.05071278,4.31622777 C0.83737186,3.66869665 1.31375252,3 1.98839574,3 L10.8840374,3 C11.4299182,3 11.872442,3.44771525 11.872442,4 C11.872442,4.55228475 11.4299182,5 10.8840374,5 L3.35973391,5 L4.01867032,7 Z M4.67760674,9 L5.66601137,12 L14.1445893,12 L15.2193828,9 L4.67760674,9 Z M6.93041888,19 C5.83865727,19 4.95360962,18.1045695 4.95360962,17 C4.95360962,15.8954305 5.83865727,15 6.93041888,15 C8.02218048,15 8.90722813,15.8954305 8.90722813,17 C8.90722813,18.1045695 8.02218048,19 6.93041888,19 Z M13.8492513,19 C12.7574897,19 11.872442,18.1045695 11.872442,17 C11.872442,15.8954305 12.7574897,15 13.8492513,15 C14.9410129,15 15.8260605,15.8954305 15.8260605,17 C15.8260605,18.1045695 14.9410129,19 13.8492513,19 Z">
                                    </path>
                                </defs>
                                <g fill="none" fill-rule="evenodd" transform="translate(0 3)">
                                    <g transform="translate(4 10)">
                                        <mask id="cart-b" fill="#ffffff">
                                            <use xlink:href="#cart-a"></use>
                                        </mask>
                                        <use fill="#D8D8D8" xlink:href="#cart-a"></use>
                                        <g fill="#FFA0A0" mask="url(#cart-b)">
                                            <rect width="24" height="24" transform="translate(-4 -13)"></rect>
                                        </g>
                                    </g>
                                    <mask id="cart-d" fill="#ffffff">
                                        <use xlink:href="#cart-c"></use>
                                    </mask>
                                    <use fill="#000000" fill-rule="nonzero" xlink:href="#cart-c"></use>
                                    <g fill="#7600FF" mask="url(#cart-d)">
                                        <rect width="24" height="24" transform="translate(0 -3)"></rect>
                                    </g>
                                </g>
                            </g>
                        </svg>

                        <span class="mx-2 text-2xl font-semibold text-white">DUX VENTAS</span>
                    </div>
                </div>

                <nav class="mt-10">
                    <a class="flex items-center px-6 py-2 mt-4   text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 transition-colors rounded-md   hover:bg-purple-100 dark:hover:bg-purple-600"
                        href="{{ url('admin/sistema') }}">
                        <span class="ml-2 text-m"> 🏠 Inicio</span>
                    </a>
                    <a class="flex items-center px-6 py-2 mt-4  text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 transition-colors rounded-md   hover:bg-purple-100 dark:hover:bg-purple-600"
                        href="{{ url('admin/personas') }}">
                        <span class="ml-2 text-m"> 👥 Clientes y Proveedores</span>
                    </a>

                    <div x-data="{ isActive: false, open: false }">
                        <a href="#" @click="$event.preventDefault(); open = !open"
                            class="flex items-center px-6 py-2 mt-4  text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 transition-colors rounded-md   hover:bg-purple-100 dark:hover:bg-purple-600"
                            :class="{ 'bg-purple-100 dark:bg-purple-600': isActive || open }" role="button"
                            aria-haspopup="true" :aria-expanded="(open || isActive) ? 'true' : 'false'">
                            <span class="ml-2 text-m"> 📦 Depósito </span>
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
                        <div role="menu" x-show="open" class="mt-2 space-y-2 px-7" aria-label="Depósito">
                            <a class="flex items-center px-6 py-2 mt-4 text-gray-500 hover:bg-gray-700 hover:bg-opacity-25 hover:text-gray-100"
                                href="{{ url('admin/categorias') }}">
                                <span class="ml-2 text-m"> 📌 Categorías</span>
                            </a>
                            <a class="flex items-center px-6 py-2 mt-4 text-gray-500 hover:bg-gray-700 hover:bg-opacity-25 hover:text-gray-100"
                                href="{{ url('admin/articulos') }}">
                                <span class="ml-2 text-m"> 📦 Productos</span>
                            </a>
                        </div>
                    </div>
                    <div x-data="{ isActive: false, open: false }">
                        <a href="#" @click="$event.preventDefault(); open = !open"
                            class="flex items-center px-6 py-2 mt-4  text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 transition-colors rounded-md   hover:bg-purple-100 dark:hover:bg-purple-600"
                            :class="{ 'bg-purple-100 dark:bg-purple-600': isActive || open }" role="button"
                            aria-haspopup="true" :aria-expanded="(open || isActive) ? 'true' : 'false'">
                            <span class="ml-2 text-m"> 🛒 Ventas </span>
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
                        <div role="menu" x-show="open" class="mt-2 space-y-2 px-7" aria-label="Ventas">
                            <a class="flex items-center px-6 py-2 mt-4 text-gray-500 hover:bg-gray-700 hover:bg-opacity-25 hover:text-gray-100"
                                href="{{ url('admin/ventas') }}">
                                <span class="ml-2 text-m"> 🛍 Punto de Venta</span>
                            </a>
                            <a class="flex items-center px-6 py-2 mt-4 text-gray-500 hover:bg-gray-700 hover:bg-opacity-25 hover:text-gray-100"
                                href="{{ url('admin/list-ventas') }}">
                                <span class="ml-2 text-m"> 📊 Ver Ventas</span>
                            </a>
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
                            <a class="flex items-center px-6 py-2 mt-4 text-gray-500 hover:bg-gray-700 hover:bg-opacity-25 hover:text-gray-100"
                                href="{{ url('admin/compras') }}">
                                <span class="ml-2 text-m">📥 Cargar Compras</span>
                            </a>
                            <a class="flex items-center px-6 py-2 mt-4 text-gray-500 hover:bg-gray-700 hover:bg-opacity-25 hover:text-gray-100"
                                href="{{ url('admin/list-compras') }}">
                                <span class="ml-2 text-m"> 📊 Ver Compras</span>
                            </a>
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
                        <div x-data="{ notificationOpen: false }" class="relative">
                            <button @click="notificationOpen = ! notificationOpen"
                                class="flex mx-4 text-gray-600 focus:outline-none">
                                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M15 17H20L18.5951 15.5951C18.2141 15.2141 18 14.6973 18 14.1585V11C18 8.38757 16.3304 6.16509 14 5.34142V5C14 3.89543 13.1046 3 12 3C10.8954 3 10 3.89543 10 5V5.34142C7.66962 6.16509 6 8.38757 6 11V14.1585C6 14.6973 5.78595 15.2141 5.40493 15.5951L4 17H9M15 17V18C15 19.6569 13.6569 21 12 21C10.3431 21 9 19.6569 9 18V17M15 17H9"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                    </path>
                                </svg>
                            </button>

                            <div x-show="notificationOpen" @click="notificationOpen = false"
                                class="fixed inset-0 z-10 w-full h-full" style="display: none;"></div>

                            <div x-show="notificationOpen"
                                class="absolute right-0 z-10 mt-2 overflow-hidden bg-white rounded-lg shadow-xl w-80"
                                style="width: 20rem; display: none;">
                                {{-- <a href="#"
                                        class="flex items-center px-4 py-3 -mx-2 text-gray-600 hover: text-gray-400hover:bg-purple-600">
                                        <img class="object-cover w-8 h-8 mx-1 rounded-full"
                                            src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?ixlib=rb-1.2.1&amp;ixid=eyJhcHBfaWQiOjEyMDd9&amp;auto=format&amp;fit=crop&amp;w=334&amp;q=80"
                                            alt="avatar">
                                        <p class="mx-2 text-m">
                                            <span class="font-bold" href="#">Sara Salah</span> replied on the <span
                                                class="font-bold text-purple-400" href="#">Upload Image</span>
                                            artical . 2m
                                        </p>
                                    </a> --}}
                                <h6
                                    class="flex items-center px-4 py-3 -mx-2 text-gray-600 hover: text-gray-400hover:bg-purple-600">
                                    sin notificacion aun</h6>
                            </div>
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
                    <div class="max-w-xl mx-auto mt-2 px-1 sm:px-1 lg:px-1">
                        @include('livewire.accesos-directos')
                    </div>
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
    {{-- <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script> --}}
    <script>
        document.addEventListener('keydown', function(event) {
            if (event.key === "F9") { // Puedes cambiar esto a cualquier otra tecla
                event.preventDefault(); // Previene la funcionalidad predeterminada de la tecla
                document.getElementById('saveSaleButton').click(); // Simula un click en el botón de guardar
            }
        });
    </script>


</body>

</html>
