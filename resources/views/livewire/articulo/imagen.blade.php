@if($imagen)
    <img src="{{ Storage::disk(config('voyager.storage.disk', 'public'))->url($imagen) }}" alt="Imagen del artículo" class="w-16 h-16 object-cover rounded">
@else
    <span>Sin imagen</span>
@endif
