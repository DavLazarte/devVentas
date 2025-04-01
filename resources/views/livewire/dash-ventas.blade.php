<main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-200">
    <div class="container px-6 py-8 mx-auto">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center space-x-4">
                <h3 class="text-2xl font-semibold text-gray-800 dark:text-black">
                    Movimientos del
                </h3>
                <input type="date" wire:model="selectedDate"
                    class="px-3 py-2 border rounded-md dark:bg-gray-800 dark:text-white dark:border-gray-500 focus:ring focus:ring-indigo-500"
                    value="{{ $selectedDate }}">
            </div>
        </div>


        <div class="grid gap-6 mb-8 md:grid-cols-2 xl:grid-cols-3">
            <!-- Ventas Totales -->
            <div
                class="flex items-center p-4 bg-white border-l-4 border-orange-500 rounded-lg shadow-sm dark:bg-gray-800">
                <div class="p-3 mr-4 bg-gradient-to-br from-orange-400 to-orange-600 rounded-full">
                    <svg class="w-6 h-6 text-white" viewBox="0 0 28 28" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M4.19999 1.4C3.4268 1.4 2.79999 2.02681 2.79999 2.8C2.79999 3.57319 3.4268 4.2 4.19999 4.2H5.9069L6.33468 5.91114C6.33917 5.93092 6.34409 5.95055 6.34941 5.97001L8.24953 13.5705L6.99992 14.8201C5.23602 16.584 6.48528 19.6 8.97981 19.6H21C21.7731 19.6 22.4 18.9732 22.4 18.2C22.4 17.4268 21.7731 16.8 21 16.8H8.97983L10.3798 15.4H19.6C20.1303 15.4 20.615 15.1004 20.8521 14.6261L25.0521 6.22609C25.2691 5.79212 25.246 5.27673 24.991 4.86398C24.7357 4.45123 24.2852 4.2 23.8 4.2H8.79308L8.35818 2.46044C8.20238 1.83722 7.64241 1.4 6.99999 1.4H4.19999Z"
                            fill="currentColor" />
                    </svg>
                </div>
                <div>
                    <p class="mb-2 text-sm font-medium text-gray-600 dark:text-gray-800">Ventas Totales</p>
                    <p class="text-lg font-semibold text-gray-700 dark:text-gray-600">{{ $monto_total_ventas }}</p>
                </div>
            </div>

            <!-- Ventas Efectivo -->
            <div
                class="flex items-center p-4 bg-white border-l-4 border-green-500 rounded-lg shadow-sm dark:bg-gray-800">
                <div class="p-3 mr-4 bg-gradient-to-br from-green-400 to-green-600 rounded-full">
                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
                <div>
                    <p class="mb-2 text-sm font-medium text-gray-600 dark:text-gray-800">Ventas Efectivo</p>
                    <p class="text-lg font-semibold text-gray-700 dark:text-gray-600">{{ $ventas_efectivo }}</p>
                </div>
            </div>

            <!-- Ventas Transferencias -->
            <div
                class="flex items-center p-4 bg-white border-l-4 border-blue-500 rounded-lg shadow-sm dark:bg-gray-800">
                <div class="p-3 mr-4 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full">
                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75a2.25 2.25 0 0 0-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" />
                    </svg>
                </div>
                <div>
                    <p class="mb-2 text-sm font-medium text-gray-600 dark:text-gray-800">Ventas Transferencias</p>
                    <p class="text-lg font-semibold text-gray-700 dark:text-gray-600">{{ $ventas_transferencia }}</p>
                </div>
            </div>

            <!-- Ventas Cuentas -->
            <div
                class="flex items-center p-4 bg-white border-l-4 border-gray-800 rounded-lg shadow-sm dark:bg-gray-800">
                <div class="p-3 mr-4 bg-gradient-to-br from-gray-700 to-gray-900 rounded-full">
                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                    </svg>
                </div>
                <div>
                    <p class="mb-2 text-sm font-medium text-gray-600 dark:text-gray-800">Total Ventas de Cuentas y
                        Tarjetas </p>
                        <p class="text-lg font-semibold text-gray-700 dark:text-gray-600">
                            {{ number_format($ventas_tarjeta + $ventas_credito, 2) }}
                        </p>
                </div>
            </div>

            <!-- Compras Totales -->
            <div class="flex items-center p-4 bg-white border-l-4 border-red-800 rounded-lg shadow-sm dark:bg-gray-800">
                <div class="p-3 mr-4 bg-gradient-to-br from-red-700 to-red-900 rounded-full">
                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8.25 9.75h4.875a2.625 2.625 0 0 1 0 5.25H12M8.25 9.75 10.5 7.5M8.25 9.75 10.5 12m9-7.243V21.75l-3.75-1.5-3.75 1.5-3.75-1.5-3.75 1.5V4.757c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0 1 11.186 0c1.1.128 1.907 1.077 1.907 2.185Z" />
                    </svg>
                </div>
                <div>
                    <p class="mb-2 text-sm font-medium text-gray-600 dark:text-gray-800">Compras Totales</p>
                    <p class="text-lg font-semibold text-gray-700 dark:text-gray-600">{{ $monto_total_compras }}</p>
                </div>
            </div>

            <!-- Compras en Efectivo -->
            <div
                class="flex items-center p-4 bg-white border-l-4 border-lime-800 rounded-lg shadow-sm dark:bg-gray-800">
                <div class="p-3 mr-4 bg-gradient-to-br from-lime-700 to-lime-900 rounded-full">
                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                    </svg>
                </div>
                <div>
                    <p class="mb-2 text-sm font-medium text-gray-600 dark:text-gray-800">Compras en Efectivo</p>
                    <p class="text-lg font-semibold text-gray-700 dark:text-gray-600">{{ round($compras_efectivo, 2) }}
                    </p>
                </div>
            </div>

            <!-- Compras por transferencias -->
            <div
                class="flex items-center p-4 bg-white border-l-4 border-teal-800 rounded-lg shadow-sm dark:bg-gray-800">
                <div class="p-3 mr-4 bg-gradient-to-br from-teal-700 to-teal-900 rounded-full">
                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75a2.25 2.25 0 0 0-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" />
                    </svg>
                </div>
                <div>
                    <p class="mb-2 text-sm font-medium text-gray-600 dark:text-gray-800">Compras por transferencias</p>
                    <p class="text-lg font-semibold text-gray-700 dark:text-gray-600">{{ $compras_transferencia }}</p>
                </div>
            </div>

            <!-- Cobros Cuentas -->
            <div
                class="flex items-center p-4 bg-white border-l-4 border-yellow-500 rounded-lg shadow-sm dark:bg-gray-800">
                <div class="p-3 mr-4 bg-gradient-to-br from-yellow-400 to-yellow-600 rounded-full">
                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="m12.75 15 3-3m0 0-3-3m3 3h-7.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
                <div>
                    <p class="mb-2 text-sm font-medium text-gray-600 dark:text-gray-800">Cobros Cuentas</p>
                    <p class="text-lg font-semibold text-gray-700 dark:text-gray-600">{{ $ingresos }}</p>
                </div>

            </div>

            <!-- Salidas -->
            <div class="flex items-center p-4 bg-white border-l-4 border-red-600 rounded-lg shadow-sm dark:bg-gray-800">
                <div class="p-3 mr-4 bg-gradient-to-br from-red-500 to-red-700 rounded-full">
                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="m11.25 9-3 3m0 0 3 3m-3-3h7.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
                <div>
                    <div>
                        <p class="mb-2 text-sm font-medium text-gray-600 dark:text-gray-800">Salidas</p>
                        <p class="text-lg font-semibold text-gray-700 dark:text-gray-600">{{ $salidas }}</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <!-- Resumen General -->
            <div class="p-4 bg-white rounded-lg shadow dark:bg-gray-800">
                <h4 class="text-xl font-semibold mb-4">Resumen General</h4>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span>Total Ventas:</span>
                        <span class="font-bold">$ {{ number_format($ventas_totales, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-green-600">
                        <span>Ventas Efectivas:</span>
                        <span class="font-bold">$ {{ number_format($ventas_efectivas, 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Cuentas Corrientes -->
            <div class="p-4 bg-white rounded-lg shadow dark:bg-gray-800">
                <h4 class="text-xl font-semibold mb-4">Cuentas Corrientes</h4>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span>Ventas a Crédito:</span>
                        <span class="font-bold">$ {{ number_format($ventas_credito, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-blue-600">
                        <span>Pagos Recibidos:</span>
                        <span class="font-bold">$ {{ number_format($pagos_credito, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-red-600">
                        <span>Pendiente:</span>
                        <span class="font-bold">$ {{ number_format($ventas_credito - $pagos_credito, 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Desglose por Método de Pago -->
            <div class="p-4 bg-white rounded-lg shadow dark:bg-gray-800">
                <h4 class="text-xl font-semibold mb-4">Desglose de Pagos</h4>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span>Efectivo:</span>
                        <span class="font-bold">$ {{ number_format($desglose_pagos['efectivo'], 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Transferencia:</span>
                        <span class="font-bold">$ {{ number_format($desglose_pagos['transferencia'], 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Tarjeta:</span>
                        <span class="font-bold">$ {{ number_format($desglose_pagos['tarjeta'], 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        @livewire('charts')


        <div class="mt-8">
            <a href="{{ url('admin/list-ventas') }}"
                class="py-2 px-4 shadow-md no-underline rounded-full bg-yellow-600 text-white font-sans font-semibold text-sm border-blue btn-primary hover:text-white hover:bg-blue-light focus:outline-none active:shadow-none mr-2">Ver
                todas las ventas</a>
            @livewire('list.list-ventas', ['fecha' => now()->toDateString()])
        </div>



        <div class="mt-8">
            <a href="{{ url('admin/list-compras') }}"
                class="py-2 px-4 shadow-md no-underline rounded-full bg-yellow-600 text-white font-sans font-semibold text-sm border-blue btn-primary hover:text-white hover:bg-blue-light focus:outline-none active:shadow-none mr-2">Ver
                todas las compras</a>
            @livewire('list.list-compras', ['fecha' => now()->toDateString()])
        </div>

    </div>
</main>
