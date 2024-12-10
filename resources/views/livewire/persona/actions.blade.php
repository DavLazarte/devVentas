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
        class="flex items-center bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M2 3h20v18H2V3z" />
            <path d="M22 6H2m20 4H2m20 4H2" />
            <path d="M12 12h.01M10 12h.01M14 12h.01" />
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

