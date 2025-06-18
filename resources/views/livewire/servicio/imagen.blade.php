@if($imagen)
    <img src="{{ $imagen }}" alt="Imagen del servicio" class="w-16 h-16 object-cover rounded">
@else
    <span>Sin imagen</span>
@endif
