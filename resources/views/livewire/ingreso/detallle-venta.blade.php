<div class="bg-gray-50 p-4 border-t border-gray-200">
    <div class="flex justify-between mb-3">
        <div>
            <p class="text-sm mb-1"><span class="font-medium">Fecha de Venta:</span> {{ $venta->created_at->format('d/m/Y') }}</p>
            <p class="text-sm"><span class="font-medium">Tipo de Venta:</span> {{ $venta->tipo_venta }}</p>
        </div>
        <div>
            <p class="text-sm mb-1"><span class="font-medium">Forma de Pago:</span> {{ $venta->forma_de_pago }}</p>
        </div>
    </div>

    <table class="min-w-full bg-white border border-gray-200 mb-3">
        <thead>
            <tr class="bg-gray-100">
                <th class="p-2 text-left text-xs font-medium text-gray-600">PRODUCTO</th>
                <th class="p-2 text-center text-xs font-medium text-gray-600">CANTIDAD</th>
                <th class="p-2 text-right text-xs font-medium text-gray-600">PRECIO UNITARIO</th>
                <th class="p-2 text-right text-xs font-medium text-gray-600">SUBTOTAL</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($venta->detalles as $detalle)
            <tr>
                <td class="p-2 text-sm">{{ $detalle->producto->nombre }}</td>
                <td class="p-2 text-sm text-center">{{ $detalle->cantidad }}</td>
                <td class="p-2 text-sm text-right">{{ $detalle->precio_venta }}</td>
                <td class="p-2 text-sm text-right">{{ $detalle->cantidad * $detalle->precio_venta }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="flex space-x-3">
        <div class="bg-purple-100 text-purple-800 px-3 py-2 rounded-md text-sm">
            <span class="font-medium">1º PAGO:</span> ${{ $venta->pago }}
        </div>
        <div class="bg-red-100 text-red-800 px-3 py-2 rounded-md text-sm">
            <span class="font-medium">SALDO:</span> ${{ $venta->saldo }}
        </div>
        <button wire:click.prevent="generarPdf({{ $venta->id }})" class="bg-purple-500 hover:bg-purple-600 text-white py-1 px-3 rounded text-sm ml-auto">
            Descargar PDF
        </button>
    </div>
</div>
