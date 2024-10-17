<div class="flex justify-end space-x-2"> <!-- Contenedor flex con espacio entre los botones -->
    <button wire:click="$emit('ver',{{ $row->id }})" 
            class="flex items-center justify-center py-2 px-4 shadow-md no-underline rounded-full bg-yellow-600 text-white font-sans font-semibold text-sm border-blue btn-primary hover:text-white hover:bg-blue-light focus:outline-none active:shadow-none">
        <i class="fas fa-eye mr-1"></i> <!-- Icono de "ver" -->
    </button>

    @if ($row->estado === 'Activo')
        <button wire:click="$emit('confirmarCancelacionVenta', {{ $row->id }})" 
                class="flex items-center justify-center py-2 px-4 shadow-md no-underline rounded-full bg-red-600 text-white font-sans font-semibold text-sm border-blue btn-primary hover:text-white hover:bg-blue-light focus:outline-none active:shadow-none">
            <i class="fas fa-trash mr-1"></i> <!-- Icono de "cancelar" -->
        </button>
    @endif
</div>