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
                    <h2 class="text-2xl font-semibold mb-4">Detalles de la Compa</h2>
                    {{-- @dd($ver_compra->proveedor->nombre) --}}
                    <div class="flex justify-between space-x-4">
                        <div class="flex-1">
                            <p><strong>ID de Compra:</strong> {{ $ver_compra->id }}</p>
                            <p><strong>Fecha de Compra:</strong> {{ $ver_compra->created_at }}</p>
                            <p><strong>Proveedor:</strong>
                                {{ $ver_compra->proveedor ? $ver_compra->proveedor->nombre : 'Compra Rapida' }}</p>
                        </div>

                        <div class="flex-1">
                            <p><strong>Tipo de Compra:</strong> {{ $ver_compra->tipo_compra }}</p>
                            <p><strong>Forma de Pago:</strong> {{ $ver_compra->tipo_pago }}</p>
                            <p><strong>Total Venta:</strong> {{ $ver_compra->total }}</p>
                        </div>
                    </div>

                    <table class="table text-gray-400 border-separate space-y-6 text-sm w-full">
                        <thead class="bg-gray-800 text-gray-500">
                            <tr>
                                <th class="p-3">Producto</th>
                                <th class="p-3 text-left">Cantidad</th>
                                <th class="p-3 text-left">Precio Venta</th>
                                <th class="p-3 text-left">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($ver_compra->detallesCompra as $detalle)
                                <tr class="bg-gray-800">
                                    <td class="p-3">{{ $detalle->articulo->nombre }}</td>
                                    <td class="p-3">{{ $detalle->cantidad }}</td>
                                    <td class="p-3">{{ $detalle->precio_compra }}</td>
                                    <td class="p-3">{{ $detalle->cantidad * $detalle->precio_compra }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="flex gap-2 p-2">
                        <div
                            class="center relative inline-block select-none whitespace-nowrap rounded-lg bg-blue-500 py-2 px-3.5 align-baseline font-sans text-xs font-bold uppercase leading-none text-white">
                            <div class="mt-px">PAGO: {{ $ver_compra->pago }}</div>
                        </div>
                        <div
                            class="center relative inline-block select-none whitespace-nowrap rounded-lg bg-red-500 py-2 px-3.5 align-baseline font-sans text-xs font-bold uppercase leading-none text-white">
                            <div class="mt-px">SALDO: {{ $ver_compra->saldo }}</div>
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
