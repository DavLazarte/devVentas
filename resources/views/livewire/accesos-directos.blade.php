<!-- resources/views/components/dashboard/accesos-rapidos-compactos.blade.php -->
<div class="p-1">
    <div>
        <h2 class="text-xs font-medium text-center">ATAJOS</h2>
    </div>
    <div class="flex justify-center gap-3 p-3 bg-white rounded-full shadow-sm">
        <!-- Punto de Venta -->
        <a href="{{ route('ventas') }}" title="Vender" class="flex items-center justify-center px-3 py-1 bg-purple-500 hover:bg-purple-600 rounded-full text-white transition-colors duration-200 shadow-md">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
            </svg>
            <span class="text-md font-medium">VENDER</span>
        </a>

        <!-- Arqueo de Cajas -->
        <a href="{{ route('caja') }}" title="Ver Cajas" class="flex items-center justify-center px-3 py-1 bg-blue-500 hover:bg-blue-600 rounded-full text-white transition-colors duration-200 shadow-md">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            <span class="text-md font-medium">CAJAS</span>
        </a>

        <!-- Cuentas Corrientes -->
        <a href="{{ route('saldos') }}" title="Cuentas con Saldo" class="flex items-center justify-center px-3 py-1 bg-green-500 hover:bg-green-600 rounded-full text-white transition-colors duration-200 shadow-md">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
            </svg>
            <span class="text-md font-medium">SALDOS</span>
        </a>
    </div>
</div>
