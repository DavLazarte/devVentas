@if($row->tiene_variantes)
    <div class="space-y-1">
        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
            {{ $row->variantes->count() }} variantes
        </span>
        <button onclick="toggleVariantes({{ $row->idarticulo }})" 
                class="text-xs text-blue-600 hover:text-blue-800 block">
            Ver detalles
        </button>
        <div id="variantes-{{ $row->idarticulo }}" class="hidden mt-2 text-xs">
            @foreach($row->variantes->take(3) as $variante)
                <div class="text-gray-600">{{ $variante->sku }}: ${{ $variante->precio_unitario }} ({{ $variante->stock }})</div>
            @endforeach
            @if($row->variantes->count() > 3)
                <div class="text-gray-500">+ {{ $row->variantes->count() - 3 }} más...</div>
            @endif
        </div>
    </div>
@else
    <span class="text-gray-500 text-xs">Producto simple</span>
@endif

<script>
function toggleVariantes(id) {
    const element = document.getElementById('variantes-' + id);
    element.classList.toggle('hidden');
}
</script>