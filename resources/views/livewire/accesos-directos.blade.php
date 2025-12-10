<!-- resources/views/components/dashboard/accesos-rapidos-compactos.blade.php -->
<div class="w-full max-w-4xl mx-auto flex flex-wrap items-center justify-center gap-3 py-2 px-2">

    <!-- Ventas -->
    <a href="{{ route('ventas') }}"
        class="flex items-center gap-2 bg-white px-4 py-1.5 rounded-full shadow-sm border border-gray-200 hover:shadow-md hover:border-purple-300 hover:bg-purple-50 transition-all duration-200 group">
        <div
            class="flex items-center justify-center w-6 h-6 rounded-full bg-purple-100 text-purple-600 group-hover:bg-purple-200">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
            </svg>
        </div>
        <span class="text-sm font-semibold text-gray-700 group-hover:text-purple-700">Vender</span>
    </a>

    <!-- Cajas -->
    <a href="{{ route('caja') }}"
        class="flex items-center gap-2 bg-white px-4 py-1.5 rounded-full shadow-sm border border-gray-200 hover:shadow-md hover:border-blue-300 hover:bg-blue-50 transition-all duration-200 group">
        <div
            class="flex items-center justify-center w-6 h-6 rounded-full bg-blue-100 text-blue-600 group-hover:bg-blue-200">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
        </div>
        <span class="text-sm font-semibold text-gray-700 group-hover:text-blue-700">Cajas</span>
    </a>

    <!-- Saldos -->
    <a href="{{ route('saldos') }}"
        class="flex items-center gap-2 bg-white px-4 py-1.5 rounded-full shadow-sm border border-gray-200 hover:shadow-md hover:border-green-300 hover:bg-green-50 transition-all duration-200 group">
        <div
            class="flex items-center justify-center w-6 h-6 rounded-full bg-green-100 text-green-600 group-hover:bg-green-200">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
            </svg>
        </div>
        <span class="text-sm font-semibold text-gray-700 group-hover:text-green-700">Saldos</span>
    </a>

</div>
