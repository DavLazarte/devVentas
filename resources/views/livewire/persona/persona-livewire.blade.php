<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg px-4 py-4">
            <h5>Gestion de Personas</h5>

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
                class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 my-3">Nueva</button>
            @if ($isOpen)
                @include('livewire.persona.crear')
            @endif

            <!-- Formulario de búsqueda con estilos de Tailwind CSS -->
            <form wire:submit.prevent="buscar" class="my-4 flex items-center">
                <input type="text" wire:model="busqueda" placeholder="Buscar por ID o Nombre del Producto"
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
                            <th class="px-4 py-2">Tipo</th>
                            <th class="px-4 py-2">Nombre</th>
                            <th class="px-4 py-2">Dirección</th>
                            <th class="px-4 py-2">Telefono</th>
                            <th class="px-4 py-2">Mail</th>
                            <th class="px-4 py-2">ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- @dd($persona) --}}
                        @foreach ($persona as $per)
                            <tr>
                                <td class="border px-4 py-2">{{ $per->idpersona }}</td>
                                <td class="border px-4 py-2">{{ $per->tipo_persona }}</td>
                                <td class="border px-4 py-2">{{ $per->nombre }}</td>
                                <td class="border px-4 py-2">{{ $per->direccion }}</td>
                                <td class="border px-4 py-2">{{ $per->telefono }}</td>
                                <td class="border px-4 py-2">{{ $per->mail}}</td>
                                <td class="border px-4 py-2 text-center">
                                    <button wire:click="editar({{ $per->idpersona }})"
                                        class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4">Editar</button>
                                    <button wire:click="borrar({{ $per->idpersona }})"
                                        class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4">Borrar</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-2">
                {{ $persona->links() }}
            </div>
        </div>
    </div>
</div>

