<x-slot name="header">
    <h1 class="text-gray-900">Administración las recetas</h1>
</x-slot>

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg px-4 py-4">

            @if (session()->has('message'))
                <div class="bg-teal-100 rounded-b text-teal-900 px-4 py-4 shadow-md my-3" role="alert">
                    <div class="flex">
                        <div>
                            <h4>{{ session('message') }}</h4>
                        </div>
                    </div>
                </div>
            @endif


            <button wire:click="crear()"
                class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 my-3">Nueva Receta</button>
            @if ($modal)
                @include('livewire.admin.create-receta')
            @endif
             <!-- Formulario de búsqueda con estilos de Tailwind CSS -->
             <form wire:submit.prevent="buscar" class="my-4 flex items-center">
                <input type="text" wire:model="busqueda" placeholder="Buscar por ID o Nombre de la Receta"
                    class="px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring focus:border-blue-300 w-full">
                {{-- <button type="submit"
                    class="ml-2 px-4 py-2 bg-blue-500 text-white rounded-md shadow hover:bg-blue-600">
                    Buscar
                </button> --}}
            </form>

            <div class="overflow-x-auto">
                <table class="table-auto min-w-full">
                    <thead>
                        <tr class="bg-indigo-600 text-white">
                            <th class="px-4 py-2">ID</th>
                            <th class="px-4 py-2">NOMBRE</th>
                            <th class="px-4 py-2">DESCRIPCION</th>
                            <th class="px-4 py-2">PORCIONES</th>
                            {{-- <th class="px-4 py-2">STOCK</th> --}}
                            <th class="px-4 py-2">ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- @dd($receria) --}}
                        @foreach ($receta as $rec)
                            <tr>
                                <td class="border px-4 py-2">{{ $rec->id }}</td>
                                <td class="border px-4 py-2">{{ $rec->nombre }}</td>
                                <td class="border px-4 py-2">{{ $rec->descripcion }}</td>
                                <td class="border px-4 py-2">{{ $rec->porciones }}</td>
                                {{-- <td class="border px-4 py-2">{{ $rec->stock }}</td> --}}
                                <td class="border px-4 py-2 text-center">
                                    <button wire:click="editar({{ $rec->id }})"
                                        class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4">Editar</button>
                                    <button wire:click="borrar({{ $rec->id }})"
                                        class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4">Borrar</button>
                                    {{-- <button wire:click="ver({{ $rec->id }})"
                                        class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4">Info</button> --}}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-2">
                {{ $receta->links() }}
            </div>
        </div>
    </div>
</div>
