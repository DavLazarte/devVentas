<!-- resources/views/components/dashboard/accesos-rapidos-compactos.blade.php -->
<div class="px-2 py-3">
    <div class="mb-2 flex items-center justify-between px-1">
        <h2 class="text-xs font-bold text-gray-500 uppercase tracking-wider">Accesos Rápidos</h2>
    </div>

    <div class="grid grid-cols-3 gap-3">
        <!-- Punto de Venta -->
        <a href="{{ route('ventas') }}"
            class="group relative flex flex-col items-center justify-center p-3 bg-white rounded-2xl shadow-sm border border-gray-100 transition-all duration-200 hover:shadow-md hover:-translate-y-1 active:scale-95">
            <div
                class="flex items-center justify-center w-10 h-10 mb-2 rounded-xl bg-purple-100 text-purple-600 group-hover:bg-purple-600 group-hover:text-white transition-colors duration-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
            </div>
            <span class="text-xs font-semibold text-gray-700 group-hover:text-purple-600">Vender</span>
        </a>

        <!-- Arqueo de Cajas -->
        <a href="{{ route('caja') }}"
            class="group relative flex flex-col items-center justify-center p-3 bg-white rounded-2xl shadow-sm border border-gray-100 transition-all duration-200 hover:shadow-md hover:-translate-y-1 active:scale-95">
            <div
                class="flex items-center justify-center w-10 h-10 mb-2 rounded-xl bg-blue-100 text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition-colors duration-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
            <span class="text-xs font-semibold text-gray-700 group-hover:text-blue-600">Cajas</span>
        </a>

        <!-- Cuentas Corrientes -->
        <a href="{{ route('saldos') }}"
            class="group relative flex flex-col items-center justify-center p-3 bg-white rounded-2xl shadow-sm border border-gray-100 transition-all duration-200 hover:shadow-md hover:-translate-y-1 active:scale-95">
            <div
                class="flex items-center justify-center w-10 h-10 mb-2 rounded-xl bg-green-100 text-green-600 group-hover:bg-green-600 group-hover:text-white transition-colors duration-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
            </div>
            <span class="text-xs font-semibold text-gray-700 group-hover:text-green-600">Saldos</span>
        </a>
    </div>
</div>
