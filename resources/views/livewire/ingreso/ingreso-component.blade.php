<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg px-4 py-4">
            <h5>Ingresos</h5>

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
                class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 my-3">Nuevo Ingreso</button>
            @if ($isOpen)
                @include('livewire.ingreso.create')
            @endif

            <!-- Formulario de búsqueda con estilos de Tailwind CSS -->
            <form wire:submit.prevent="buscar" class="my-4 flex items-center">
                <input type="text" wire:model="busqueda" placeholder="Buscar por ID o Nombre del cliente"
                    class="px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring focus:border-blue-300 w-full">
            </form>



            <div class="overflow-x-auto">
                <table class="table-auto min-w-full">
                    <thead>
                        <tr class="bg-indigo-600 text-white">
                            <th class="px-4 py-2">ID</th>
                            <th class="px-4 py-2">Cliente</th>
                            <th class="px-4 py-2">Monto</th>
                            <th class="px-4 py-2">Descripcion</th>
                            <th class="px-4 py-2">Saldo</th>
                            <th class="px-4 py-2">Estado</th>
                            <th class="px-4 py-2">ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($ingreso as $ing)
                            <tr>
                                <td class="border px-4 py-2">{{ $ing->id_ingreso }}</td>
                                <td class="border px-4 py-2">{{ $ing->cliente->nombre }}</td>
                                <td class="border px-4 py-2">{{ number_format($ing->monto, 2) }}</td>
                                <td class="border px-4 py-2">{{ $ing->descripcion }}</td>
                                <td class="border px-4 py-2">{{ number_format($ing->saldo, 2) }}</td>
                                <td class="border px-4 py-2">{{ $ing->estado }}</td>
                                <td class="border px-4 py-2 text-center">
                                    {{-- <button wire:click="editar({{ $ing->id_ingreso }})"
                                        class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4">Editar</button> --}}
                                    <button wire:click="borrar({{ $ing->id_ingreso }})"
                                        class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4">Borrar</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-2">
                {{ $ingreso->links() }}
            </div>
        </div>
    </div>
</div>

