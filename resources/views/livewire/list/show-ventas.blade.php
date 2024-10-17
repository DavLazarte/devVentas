<div class="fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 text-center sm:block sm:p-0">
        
        <div class="fixed inset-0 transition-opacity">
            <div class="absolute inset-0 bg-black opacity-50"></div>
        </div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:align-middle sm:max-w-3xl sm:w-full">
            <div class="bg-white p-8">
                <!-- Encabezado -->
                <div class="mb-6">
                    <h2 class="text-2xl font-bold text-gray-800">Detalles de la Venta</h2>
                </div>

                <!-- Información General de la Venta -->
                <div class="grid grid-cols-2 gap-6 mb-8">
                    <div class="bg-gray-50 p-4 rounded-lg shadow">
                        <h3 class="text-lg font-semibold text-gray-700 mb-2">Información de Venta</h3>
                        <p class="text-sm"><strong>ID de Venta:</strong> {{ $ver_venta->id }}</p>
                        <p class="text-sm"><strong>Fecha:</strong> {{ $ver_venta->created_at }}</p>
                        <p class="text-sm"><strong>Cliente:</strong> {{ $ver_venta->persona ? $ver_venta->persona->nombre : 'Consumidor Final' }}</p>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg shadow">
                        <h3 class="text-lg font-semibold text-gray-700 mb-2">Resumen de Pago</h3>
                        <p class="text-sm"><strong>Tipo de Venta:</strong> {{ $ver_venta->tipo_venta }}</p>
                        <p class="text-sm"><strong>Forma de Pago:</strong> {{ $ver_venta->forma_de_pago }}</p>
                        <p class="text-sm"><strong>Total:</strong> ${{ $ver_venta->total_venta }}</p>
                    </div>
                </div>

                <!-- Tabla de Productos -->
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Productos</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white border border-gray-200">
                            <thead class="bg-gray-100 text-gray-700">
                                <tr>
                                    <th class="p-3 text-left text-sm font-medium">Producto</th>
                                    <th class="p-3 text-left text-sm font-medium">Cantidad</th>
                                    <th class="p-3 text-left text-sm font-medium">Precio Unitario</th>
                                    <th class="p-3 text-left text-sm font-medium">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach ($ver_venta->detalles as $detalle)
                                    <tr>
                                        <td class="p-3 text-sm text-gray-600">{{ $detalle->producto->nombre }}</td>
                                        <td class="p-3 text-sm text-gray-600">{{ $detalle->cantidad }}</td>
                                        <td class="p-3 text-sm text-gray-600">${{ $detalle->precio_venta }}</td>
                                        <td class="p-3 text-sm text-gray-600">${{ $detalle->cantidad * $detalle->precio_venta }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Información de Pago -->
                <div class="flex justify-between items-center mb-6">
                    <div class="bg-blue-600 text-white rounded-lg px-4 py-2 font-semibold">
                        PAGO: ${{ $ver_venta->pago }}
                    </div>
                    <div class="bg-red-600 text-white rounded-lg px-4 py-2 font-semibold">
                        SALDO: ${{ $ver_venta->saldo }}
                    </div>
                </div>

                <!-- Botón de cerrar -->
                <div class="text-right">
                    <button wire:click="closeModal()" type="button"
                        class="inline-flex justify-center rounded-md border border-gray-300 px-4 py-2 bg-gray-100 text-gray-800 text-sm font-semibold shadow-sm hover:bg-gray-200 transition ease-in-out duration-150">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
