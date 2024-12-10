<div class="p-4 bg-white rounded-lg shadow-md">
    <div class="flex justify-between items-center mb-4">
        <div class="flex-1">
            <p class="text-sm text-gray-600"><strong>Fecha de Venta:</strong> {{ $venta->created_at->format('d/m/Y') }}</p>
        </div>

        <div class="flex-1 text-right">
            <p class="text-sm text-gray-600"><strong>Tipo de Venta:</strong> {{ $venta->tipo_venta }}</p>
            <p class="text-sm text-gray-600"><strong>Forma de Pago:</strong> {{ $venta->forma_de_pago }}</p>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full bg-white divide-y divide-gray-200 rounded-md shadow">
            <thead class="bg-gray-100">
                <tr>
                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto</th>
                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cantidad</th>
                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Precio Unitario</th>
                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subtotal</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach ($venta->detalles as $detalle)
                <tr class="hover:bg-gray-50">
                    <td class="py-3 px-4 whitespace-nowrap text-sm text-gray-700">{{ $detalle->producto->nombre }}</td>
                    <td class="py-3 px-4 whitespace-nowrap text-sm text-gray-700">{{ $detalle->cantidad }}</td>
                    <td class="py-3 px-4 whitespace-nowrap text-sm text-gray-700">{{ $detalle->precio_venta }}</td>
                    <td class="py-3 px-4 whitespace-nowrap text-sm text-gray-700">{{ $detalle->cantidad * $detalle->precio_venta }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="flex gap-2 mt-4">
        <div class="relative inline-block select-none whitespace-nowrap rounded-lg bg-blue-500 py-2 px-3.5 text-xs font-bold uppercase text-white">
            <div>PAGO: {{ $venta->pago }}</div>
        </div>
        <div class="relative inline-block select-none whitespace-nowrap rounded-lg bg-red-500 py-2 px-3.5 text-xs font-bold uppercase text-white">
            <div>SALDO: {{ $venta->saldo }}</div>
        </div>
        <button wire:click.prevent="generarPdf({{ $venta->id }})" 
            class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded shadow-lg transition duration-200 ease-in-out transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-opacity-75">
            Descargar PDF
        </button>
        
    </div>
</div>