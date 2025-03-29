<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">

    @livewireStyles
    <script src="{{ mix('js/app.js') }}"></script>
    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @livewireStyles
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
                        <svg viewBox="0 0 50 50" data-name="Layer 1" id="Layer_1" xmlns="http://www.w3.org/2000/svg"
                            fill="#000000">
                            <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                            <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                            <g id="SVGRepo_iconCarrier">
                                <defs>
                                    <style>
                                        .cls-1 {
                                            fill: #231f20;
                                        }

                                        .cls-2 {
                                            fill: #ffba50;
                                        }

                                        .cls-3 {
                                            fill: #ff8e5a;
                                        }

                                        .cls-4 {
                                            fill: #7cc05b;
                                        }

                                        .cls-5 {
                                            fill: #8d7f89;
                                        }

                                        .cls-6 {
                                            fill: #00a1d3;
                                        }
                                    </style>
                                </defs>
                                <title></title>
                                <path class="cls-1"
                                    d="M43.554,38.866l1.939-11.227a.488.488,0,0,0-.048-.293.339.339,0,0,0-.018-.039.374.374,0,0,0-.045-.067.491.491,0,0,0-.4-.218H26.22l-.635-3.674a1.654,1.654,0,0,0-1.633-1.376H20.763a1.442,1.442,0,0,0-1.441,1.441v.392a1.442,1.442,0,0,0,1.441,1.441H24.9l2.433,14.085a3.483,3.483,0,0,0,1.205,2.089,2.142,2.142,0,1,0,2.674.808h8.384a2.113,2.113,0,0,0-.334,1.136,2.136,2.136,0,1,0,3.73-1.407.492.492,0,0,0,.062-.229.5.5,0,0,0-.5-.5H30.771a2.481,2.481,0,0,1-2.421-1.939H43.06A.459.459,0,0,0,43.554,38.866ZM39.52,30.975H43.9l-.309,1.794a.5.5,0,0,0-.115-.023H39.3Zm-.349,2.771h4.253l-.306,1.772H38.949Zm-11.844-1a.489.489,0,0,0-.115.023l-.31-1.794H31.31l.217,1.771Zm.054,1H31.65l.217,1.772h-4.18Zm.785,4.543-.306-1.771h4.129l.217,1.771Zm9.426,0H33.213L33,36.518h4.819Zm.349-2.77H32.874l-.217-1.772h5.506Zm.348-2.772H32.534l-.217-1.771h6.2Zm4.352,5.542H38.6l.223-1.77h4.123Z">
                                </path>
                                <path class="cls-1"
                                    d="M23.44,35.2H23.1a1.384,1.384,0,0,0,.071-.419V33.5a1.412,1.412,0,0,0-1.411-1.41h-5.19a6.343,6.343,0,1,0-9.757,2.164,6.34,6.34,0,0,0,7.262,10.351,1.411,1.411,0,0,0,1.309.891H23.44a1.412,1.412,0,0,0,1.41-1.41V42.814a1.412,1.412,0,0,0-1.41-1.41H23.1a1.408,1.408,0,0,0,.07-.418V39.711a1.408,1.408,0,0,0-.07-.418h.341a1.412,1.412,0,0,0,1.41-1.41V36.607A1.412,1.412,0,0,0,23.44,35.2Z">
                                </path>
                                <path class="cls-2"
                                    d="M5.5,39.153A5.347,5.347,0,1,1,10.847,44.5,5.354,5.354,0,0,1,5.5,39.153Z"></path>
                                <path class="cls-2"
                                    d="M23.85,42.814V44.09a.411.411,0,0,1-.41.41H15.382a.411.411,0,0,1-.411-.41.494.494,0,0,0-.02-.1A6.374,6.374,0,0,0,16.288,42.4H23.44A.411.411,0,0,1,23.85,42.814Z">
                                </path>
                                <path class="cls-1"
                                    d="M10.847,34.7A4.449,4.449,0,1,0,15.3,39.153,4.454,4.454,0,0,0,10.847,34.7Z">
                                </path>
                                <path class="cls-3"
                                    d="M10.847,42.6A3.449,3.449,0,1,1,14.3,39.153,3.453,3.453,0,0,1,10.847,42.6Z">
                                </path>
                                <path class="cls-3"
                                    d="M16.774,41.4a6.284,6.284,0,0,0,.413-2.1h4.571a.41.41,0,0,1,.411.41v1.275a.41.41,0,0,1-.411.41c-.013,0-.025.007-.038.008Z">
                                </path>
                                <path class="cls-1"
                                    d="M11.026,38.651l-.361,0a.414.414,0,0,1,0-.828h1.067a.5.5,0,0,0,0-1h-.385v-.4a.5.5,0,0,0-1,0v.438a1.412,1.412,0,0,0,.321,2.789l.362,0a.414.414,0,0,1,0,.828H9.962a.5.5,0,0,0,0,1h.385v.4a.5.5,0,0,0,1,0V41.44a1.412,1.412,0,0,0-.321-2.789Z">
                                </path>
                                <path class="cls-2"
                                    d="M5.5,29.369a5.347,5.347,0,1,1,8.515,4.292,6.295,6.295,0,0,0-6.335,0A5.325,5.325,0,0,1,5.5,29.369Z">
                                </path>
                                <path class="cls-1"
                                    d="M16.974,16.381a3.3,3.3,0,0,0,2.794-1.561l6.583,2.743a3.305,3.305,0,1,0,6.01-1.1L39.932,10.2a3.424,3.424,0,1,0-.609-.795l-7.632,6.311a3.26,3.26,0,0,0-4.975.917L20.161,13.9a3.3,3.3,0,1,0-3.187,2.482Z">
                                </path>
                                <path class="cls-4" d="M42.2,5.5a2.3,2.3,0,1,1-2.3,2.3A2.306,2.306,0,0,1,42.2,5.5Z">
                                </path>
                                <path class="cls-4"
                                    d="M29.585,15.934a2.3,2.3,0,1,1-2.3,2.3A2.306,2.306,0,0,1,29.585,15.934Z"></path>
                                <path class="cls-4"
                                    d="M16.974,10.773a2.3,2.3,0,1,1-2.3,2.3A2.306,2.306,0,0,1,16.974,10.773Z"></path>
                                <path class="cls-5"
                                    d="M20.763,24.246a.442.442,0,0,1-.441-.441v-.392a.442.442,0,0,1,.441-.441h3.189a.657.657,0,0,1,.648.546l.126.728Z">
                                </path>
                                <path class="cls-6"
                                    d="M29.409,44.5a1.136,1.136,0,1,1,1.136-1.136A1.137,1.137,0,0,1,29.409,44.5Z">
                                </path>
                                <circle class="cls-6" cx="41.396" cy="43.364" r="1.136"></circle>
                                <polygon class="cls-5"
                                    points="44.412 28.022 44.075 29.975 26.73 29.975 26.392 28.022 44.412 28.022">
                                </polygon>
                                <path class="cls-3"
                                    d="M21.758,33.094a.41.41,0,0,1,.411.41v1.274a.411.411,0,0,1-.411.411c-.013,0-.025.007-.038.008H15.8a6.325,6.325,0,0,0-.919-.939,6.4,6.4,0,0,0,1.1-1.164Z">
                                </path>
                                <path class="cls-2"
                                    d="M23.85,37.883a.411.411,0,0,1-.41.41H21.758c-.013,0-.025.007-.038.008H17.13a6.3,6.3,0,0,0-.67-2.1h6.98a.411.411,0,0,1,.41.41Z">
                                </path>
                            </g>
                        </svg>

                        <span class="mx-2 text-2xl font-semibold text-white">DEV-POS</span>
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
                                href="{{ url('admin/ingresos') }}">
                                <span class="ml-2 text-m">🏦 Cuentas Corrientes</span>
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
                                <h6 class="flex items-center px-4 py-3 -mx-2 text-gray-600 hover: text-gray-400hover:bg-purple-600">sin notificacion aun</h6>
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
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf

                                        <x-dropdown-link :href="route('logout')"
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
