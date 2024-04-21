<div class="flex justify-between space-x-4">
    <div class="flex-1">
        <p><strong>Fecha de Venta:</strong> {{ $venta->created_at }}</p>
    </div>

    <div class="flex-1">
        <p><strong>Tipo de Venta:</strong> {{ $venta->tipo_venta }}</p>
        <p><strong>Forma de Pago:</strong> {{ $venta->forma_de_pago }}</p>
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
        @foreach ($venta->detalles as $detalle)
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
        <div class="mt-px">PAGO: {{ $venta->pago }}</div>
    </div>
    <div
        class="center relative inline-block select-none whitespace-nowrap rounded-lg bg-red-500 py-2 px-3.5 align-baseline font-sans text-xs font-bold uppercase leading-none text-white">
        <div class="mt-px">SALDO: {{ $venta->saldo }}</div>
    </div>
</div>

