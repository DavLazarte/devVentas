<div class="fixed inset-0 z-50 overflow-y-auto">
    <div class="flex justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">

        <div class="fixed inset-0 transition-opacity">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full"
            role="dialog" aria-modal="true" aria-labelledby="modal-headline">

            <div class="max-w-4xl mx-auto mt-8">
                <!-- Card principal -->
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <!-- Información de la compra -->
                    <h2 class="text-3xl font-bold text-gray-800 mb-4">Detalles de la Compra</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <p class="text-lg font-semibold text-gray-700">
                                <strong>ID de Compra:</strong> {{ $ver_compra->id }}
                            </p>
                            <p class="text-lg font-semibold text-gray-700">
                                <strong>Fecha de Compra:</strong> {{ $ver_compra->created_at }}
                            </p>
                            <p class="text-lg font-semibold text-gray-700">
                                <strong>Proveedor:</strong> 
                                {{ $ver_compra->proveedor ? $ver_compra->proveedor->nombre : 'Compra Rápida' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-lg font-semibold text-gray-700">
                                <strong>Tipo de Compra:</strong> {{ $ver_compra->tipo_compra }}
                            </p>
                            <p class="text-lg font-semibold text-gray-700">
                                <strong>Forma de Pago:</strong> {{ $ver_compra->tipo_pago }}
                            </p>
                            <p class="text-lg font-semibold text-gray-700">
                                <strong>Total Compra:</strong> {{ $ver_compra->total }}
                            </p>
                        </div>
                    </div>

                    <!-- Tabla de detalles de la compra -->
                    <div class="bg-white rounded-lg shadow">
                        <table class="min-w-full table-auto">
                            <thead class="bg-purple-600 text-white">
                                <tr>
                                    <th class="px-4 py-2">Producto</th>
                                    <th class="px-4 py-2 text-left">Cantidad</th>
                                    <th class="px-4 py-2 text-left">Precio Compra</th>
                                    <th class="px-4 py-2 text-left">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white">
                                @foreach ($ver_compra->detallesCompra as $detalle)
                                    <tr class="border-b">
                                        <td class="px-4 py-2">{{ $detalle->articulo->nombre }}</td>
                                        <td class="px-4 py-2">{{ $detalle->cantidad }}</td>
                                        <td class="px-4 py-2">{{ $detalle->precio_compra }}</td>
                                        <td class="px-4 py-2">{{ $detalle->cantidad * $detalle->precio_compra }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Sección de pago y saldo -->
                    <div class="flex gap-2 p-2 mt-4">
                        <div
                            class="center relative inline-block select-none whitespace-nowrap rounded-lg bg-blue-500 py-2 px-3.5 align-baseline font-sans text-xs font-bold uppercase leading-none text-white">
                            <div class="mt-px">PAGO: {{ $ver_compra->pago }}</div>
                        </div>
                        <div
                            class="center relative inline-block select-none whitespace-nowrap rounded-lg bg-red-500 py-2 px-3.5 align-baseline font-sans text-xs font-bold uppercase leading-none text-white">
                            <div class="mt-px">SALDO: {{ $ver_compra->saldo }}</div>
                        </div>
                    </div>

                    <!-- Botón de cerrar -->
                    <div class="mt-4 text-right">
                        <button wire:click="closeModal()" type="button"
                            class="inline-flex justify-center w-auto rounded-md border border-gray-300 px-4 py-2 bg-gray-200 text-base font-medium text-gray-700 shadow-sm hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-600 transition ease-in-out duration-150 sm:text-sm">
                            Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
