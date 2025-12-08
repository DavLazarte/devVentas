<!-- resources/views/livewire/pedido/actions.blade.php -->
<div class="flex space-x-1 justify-center">
    <!-- Ver Detalle -->
    <button wire:click="verDetalle({{ $row->id }})" title="Ver Detalle"
        class="group flex items-center justify-center w-8 h-8 bg-white border border-gray-200 hover:bg-green-50 hover:border-green-200 text-gray-500 hover:text-green-600 rounded-lg transition-all duration-200 shadow-sm">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
        </svg>
    </button>

    <!-- Gestionar Estado -->
    <button wire:click="editar({{ $row->id }})" title="Cambiar Estado"
        class="group flex items-center justify-center w-8 h-8 bg-white border border-gray-200 hover:bg-blue-50 hover:border-blue-200 text-gray-500 hover:text-blue-600 rounded-lg transition-all duration-200 shadow-sm">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
    </button>

    <!-- Eliminar -->
    <button wire:click="eliminar({{ $row->id }})" title="Eliminar"
        class="group flex items-center justify-center w-8 h-8 bg-white border border-gray-200 hover:bg-red-50 hover:border-red-200 text-gray-500 hover:text-red-600 rounded-lg transition-all duration-200 shadow-sm">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
        </svg>
    </button>
</div>
