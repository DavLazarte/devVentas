<div class="flex space-x-1 justify-around">
    <button wire:click="editar({{ $row->idpersona }})" class="flex items-center bg-blue-400 hover:bg-blue-500 text-white font-bold py-2 px-4 rounded">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M4 17.414l7-7 3.536 3.536-7 7H4v-3.536z" />
        </svg>
        
    </button>
    <button wire:click="borrar({{ $row->idpersona }})" class="flex items-center bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
    </button>
</div>
