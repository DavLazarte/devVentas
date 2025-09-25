<div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-5xl mx-4 overflow-hidden">
        
        <!-- Encabezado -->
        <div class="bg-blue-600 px-6 py-4 flex items-center justify-between">
            <h2 class="text-2xl font-bold text-white flex items-center gap-2">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3 3h18v18H3V3z M8 8h8v8H8V8z" />
                </svg>
                Detalles de la Venta
            </h2>
            <button wire:click="closeModal()" class="text-white hover:text-gray-200 transition">
                ✕
            </button>
        </div>

        <!-- Contenido -->
        <div class="p-8 space-y-8">
            
            <!-- Información General -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-gray-50 p-5 rounded-lg shadow-md">
                    <h3 class="text-lg font-semibold text-gray-700 mb-3">Información de Venta</h3>
                    <p class="text-sm"><strong>ID de Venta:</strong> {{ $ver_venta->id }}</p>
                    <p class="text-sm"><strong>Fecha:</strong> {{ $ver_venta->created_at }}</p>
                    <p class="text-sm"><strong>Cliente:</strong> {{ $ver_venta->persona ? $ver_venta->persona->nombre : 'Consumidor Final' }}</p>
                </div>
                <div class="bg-gray-50 p-5 rounded-lg shadow-md">
                    <h3 class="text-lg font-semibold text-gray-700 mb-3">Resumen de Pago</h3>
                    <p class="text-sm"><strong>Tipo de Venta:</strong> {{ $ver_venta->tipo_venta }}</p>
                    <p class="text-sm"><strong>Forma de Pago:</strong> {{ $ver_venta->forma_de_pago }}</p>
                    <p class="text-sm"><strong>Total:</strong> ${{ $ver_venta->total_venta }}</p>
                </div>
            </div>

            <!-- Tabla de Productos -->
            <div>
                <h3 class="text-lg font-semibold text-gray-700 mb-4">Productos</h3>
                <div class="overflow-x-auto rounded-lg border border-gray-200">
                    <table class="min-w-full bg-white">
                        <thead class="bg-blue-600 text-white">
                            <tr>
                                <th class="p-3 text-left text-sm font-medium">Producto</th>
                                <th class="p-3 text-left text-sm font-medium">Cantidad</th>
                                <th class="p-3 text-left text-sm font-medium">Precio Unitario</th>
                                <th class="p-3 text-left text-sm font-medium">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach ($ver_venta->detalles as $detalle)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="p-3 text-sm text-gray-700">
                                        {{ $detalle->producto->nombre }}
                                        @if ($detalle->variante)
                                            <span class="text-xs text-gray-500"> - {{ $detalle->variante->descripcion_variante }}</span>
                                        @endif
                                    </td>
                                    <td class="p-3 text-sm text-gray-700">{{ $detalle->cantidad }}</td>
                                    <td class="p-3 text-sm text-gray-700">${{ $detalle->precio_venta }}</td>
                                    <td class="p-3 text-sm text-gray-700 font-semibold">${{ $detalle->cantidad * $detalle->precio_venta }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Totales -->
            <div class="flex justify-between items-center">
                <div class="bg-green-600 text-white rounded-lg px-6 py-3 font-bold shadow">
                    PAGO: ${{ $ver_venta->pago }}
                </div>
                <div class="bg-red-600 text-white rounded-lg px-6 py-3 font-bold shadow">
                    SALDO: ${{ $ver_venta->saldo }}
                </div>
            </div>

            <!-- Botón Cerrar -->
            <div class="text-right">
                <button wire:click="closeModal()" 
                    class="inline-flex justify-center rounded-lg border border-gray-300 px-5 py-2 bg-gray-100 text-gray-800 text-sm font-semibold shadow hover:bg-gray-200 transition">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</div>
