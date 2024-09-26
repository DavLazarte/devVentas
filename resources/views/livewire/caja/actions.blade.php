<div class="flex space-x-1 justify-around">
    @if ($row->estado === 'abierta')
        <button wire:click="editar({{ $row->id }})"
            class="flex items-center bg-red-700 hover:bg-red-800 text-white font-bold py-2 px-4 rounded">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
            Cerrar Caja
        </button>
    @else
        <button wire:click="verCaja({{ $row->id }})"
            class="flex items-center bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12h.01M12 12h.01M9 12h.01M12 9v.01M12 15v.01M6 18L18 6M6 6l12 12" />
            </svg>
            Ver
        </button>
    @endif
</div>
