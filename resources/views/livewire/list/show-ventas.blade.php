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
                    <!-- Información de la venta -->
                    <h2 class="text-2xl font-semibold mb-4">Detalles de la Venta</h2>
                    <div class="flex justify-between space-x-4">
                        <div class="flex-1">
                            <p><strong>ID de Venta:</strong> {{ $ver_venta->id }}</p>
                            <p><strong>Fecha de Venta:</strong> {{ $ver_venta->created_at }}</p>
                            <p><strong>Cliente:</strong>
                                {{ $ver_venta->persona ? $ver_venta->persona->nombre : 'Consumidor Final' }}</p>
                        </div>

                        <div class="flex-1">
                            <p><strong>Tipo de Venta:</strong> {{ $ver_venta->tipo_venta }}</p>
                            <p><strong>Forma de Pago:</strong> {{ $ver_venta->forma_de_pago }}</p>
                            <p><strong>Total Venta:</strong> {{ $ver_venta->total_venta }}</p>
                        </div>
                    </div>

                    <table class="table text-gray-400 border-separate space-y-6 text-sm w-full">
                        <thead class="bg-gray-800 text-gray-500">
                            <tr>
                                <th class="p-3">Producto</th>
                                <th class="p-3 text-left">Cantidad</th>
                                <th class="p-3 text-left">Precio Unitario</th>
                                <th class="p-3 text-left">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($ver_venta->detalles as $detalle)
                                <tr class="bg-gray-800">
                                    <td class="p-3">{{ $detalle->producto->nombre }}</td>
                                    <td class="p-3">{{ $detalle->cantidad }}</td>
                                    <td class="p-3">{{ $detalle->precio_venta }}</td>
                                    <td class="p-3">{{ $detalle->cantidad * $detalle->precio_venta }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="flex gap-2 p-2">
                        <div
                            class="center relative inline-block select-none whitespace-nowrap rounded-lg bg-blue-500 py-2 px-3.5 align-baseline font-sans text-xs font-bold uppercase leading-none text-white">
                            <div class="mt-px">PAGO: {{ $ver_venta->pago }}</div>
                        </div>
                        <div
                            class="center relative inline-block select-none whitespace-nowrap rounded-lg bg-red-500 py-2 px-3.5 align-baseline font-sans text-xs font-bold uppercase leading-none text-white">
                            <div class="mt-px">SALDO: {{ $ver_venta->saldo }}</div>
                        </div>
                    </div>

                    <span class="flex w-50 rounded-md shadow-sm sm:ml-3 sm:w-auto">
                        <button wire:click="closeModal()" type="button"
                            class="inline-flex justify-center w-full rounded-md border border-gray-300 px-4 py-2 bg-gray-200 text-base leading-6 font-medium text-gray-700 shadow-sm hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:shadow-outline-blue transition ease-in-out duration-150 sm:text-sm sm:leading-5">Cerrar</button>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>
