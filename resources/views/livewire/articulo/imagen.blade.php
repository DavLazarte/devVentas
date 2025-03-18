@if($imagen)
    <img src="{{ asset('storage/articulos/' . $imagen) }}" alt="Imagen del artículo" class="w-16 h-16 object-cover rounded">
@else
    <span>Sin imagen</span>
@endif
