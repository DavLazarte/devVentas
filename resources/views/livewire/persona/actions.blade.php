<!-- resources/views/livewire/articulo/actions.blade.php -->
<div class="flex space-x-1 justify-around">
    <button wire:click="editar({{ $row->idpersona }})"
        class="flex items-center bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15.232 5.232l3.536 3.536M4 17.414l7-7 3.536 3.536-7 7H4v-3.536z" />
        </svg>

    </button>

    <button wire:click="mostrarPagos({{ $row->idpersona }})"
        wire:loading.attr="disabled"
        wire:loading.class="opacity-50 cursor-not-allowed"
        wire:target="mostrarPagos"
        class="flex items-center bg-green-500 hover:bg-green-600 text-white text-sm font-bold py-2 px-4 rounded relative">

        <span wire:loading.remove wire:target="mostrarPagos">Ver Cuenta</span>
        <span wire:loading wire:target="mostrarPagos">Abriendo...</span>

        <!-- Loader animado -->
        <svg wire:loading wire:target="mostrarPagos" class="animate-spin h-4 w-4 ml-2 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0116 0h-2a6 6 0 10-12 0H4z"></path>
        </svg>

    </button>



    <button wire:click="borrar({{ $row->idpersona }})"
        class="flex items-center bg-red-700 hover:bg-red-800 text-white font-bold py-2 px-4 rounded">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>

    </button>

</div>

