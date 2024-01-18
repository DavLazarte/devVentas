<x-slot name="header">
    <h1 class="text-gray-900">Administración de Categorias</h1>
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
                class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 my-3">Nuevo</button>
            @if ($isOpen)
                @include('livewire.categorias.crear')
            @endif

            <!-- Formulario de búsqueda con estilos de Tailwind CSS -->
            <form wire:submit.prevent="buscar" class="my-4 flex items-center">
                <input type="text" wire:model="busqueda" placeholder="Buscar por ID o Nombre de la categoria"
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
                            <th class="px-4 py-2">id</th>
                            <th class="px-4 py-2">Nombre</th>
                            <th class="px-4 py-2">Descripción</th>
                            <th class="px-4 py-2">Estado</th>
                            <th class="px-4 py-2">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- @dd($categoriasa) --}}
                        @foreach ($categoria as $cat)
                            <tr>
                                <td class="border px-4 py-2">{{ $cat->id_categoria }}</td>
                                <td class="border px-4 py-2">{{ $cat->nombre }}</td>
                                <td class="border px-4 py-2">{{ $cat->descripcion }}</td>
                                <td class="border px-4 py-2">{{ $cat->estado }}</td>
                                <td class="border px-4 py-2 text-center">
                                    <button wire:click="editar({{ $cat->id_categoria }})"
                                        class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4">Editar</button>
                                    <button wire:click="borrar({{ $cat->id_categoria }})"
                                        class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4">Borrar</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-2">
                {{ $categoria->links() }}
            </div>
        </div>
    </div>
