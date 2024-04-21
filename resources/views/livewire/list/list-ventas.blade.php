<x-slot name="header">
</x-slot>
<div class="py-12">
    <div class="max-w-12xl mx-auto sm:px6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg px-4 py-4">
            <h1 class="text-gray-900">Ventas</h1>


            @if (session()->has('message'))
                <div class="bg-teal-100 rounded-b text-teal-900 px-4 py-4 shadow-md my-3" role="alert">
                    <div class="flex">
                        <div>
                            <h4>{{ session('message') }}</h4>
                        </div>
                    </div>
                </div>
            @endif




            <form wire:submit.prevent="buscar" class="my-4 flex items-center">
                <input type="text" wire:model="busqueda"
                    placeholder="Buscar por fecha, cliente , tipo o forma de pago"
                    class="px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring focus:border-blue-300 w-full">

            </form>
            <div class="flex flex-col mt-8">
                <div class="py-2 -my-2 overflow-x-auto sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
                    <div
                        class="inline-block min-w-full overflow-hidden align-middle border-b border-gray-200 shadow sm:rounded-lg">
                        <table class="min-w-full">
                            <thead>
                                <tr>
                                    <th
                                        class="px-6 py-3 text-xs font-medium leading-4 tracking-wider text-left text-gray-500 uppercase border-b border-gray-200 bg-gray-50">
                                        id</th>
                                    <th
                                        class="px-6 py-3 text-xs font-medium leading-4 tracking-wider text-left text-gray-500 uppercase border-b border-gray-200 bg-gray-50">
                                        Fecha</th>
                                    <th
                                        class="px-6 py-3 text-xs font-medium leading-4 tracking-wider text-left text-gray-500 uppercase border-b border-gray-200 bg-gray-50">
                                        Cliente</th>

                                    <th
                                        class="px-6 py-3 text-xs font-medium leading-4 tracking-wider text-left text-gray-500 uppercase border-b border-gray-200 bg-gray-50">
                                        Tipo de Venta</th>
                                    <th
                                        class="px-6 py-3 text-xs font-medium leading-4 tracking-wider text-left text-gray-500 uppercase border-b border-gray-200 bg-gray-50">
                                        Total Venta</th>
                                    <th
                                        class="px-6 py-3 text-xs font-medium leading-4 tracking-wider text-left text-gray-500 uppercase border-b border-gray-200 bg-gray-50">
                                        Pago</th>
                                    <th
                                        class="px-6 py-3 text-xs font-medium leading-4 tracking-wider text-left text-gray-500 uppercase border-b border-gray-200 bg-gray-50">
                                        Forma de Pago</th>
                                    <th
                                        class="px-6 py-3 text-xs font-medium leading-4 tracking-wider text-left text-gray-500 uppercase border-b border-gray-200 bg-gray-50">
                                        Saldo</th>
                                    <th
                                        class="px-6 py-3 text-xs font-medium leading-4 tracking-wider text-left text-gray-500 uppercase border-b border-gray-200 bg-gray-50">
                                        Estado</th>
                                    <th
                                        class="px-6 py-3 text-xs text-center font-medium leading-4 tracking-wider  text-gray-500 uppercase border-b border-gray-200 bg-gray-50">
                                        Acciones</th>

                                </tr>
                            </thead>

                            <tbody class="bg-white">
                                @foreach ($ventas as $ven)
                                    <tr>
                                        <td
                                            class="px-6 py-4 text-sm leading-5 text-gray-500 whitespace-no-wrap border-b border-gray-200">
                                            {{ $ven->id }}</td>
                                        <td
                                            class="px-6 py-4 text-sm leading-5 text-gray-500 whitespace-no-wrap border-b border-gray-200">
                                            {{ $ven->created_at->format('Y-m-d') }}</td>
                                        <td
                                            class="px-6 py-4 text-sm leading-5 text-gray-500 whitespace-no-wrap border-b border-gray-200">
                                            {{ optional($ven->persona)->nombre ?? 'Consumidor Final' }}</td>
                                        <td
                                            class="px-6 py-4 text-sm leading-5 text-gray-500 whitespace-no-wrap border-b border-gray-200">
                                            {{ $ven->tipo_venta }}</td>
                                        <td
                                            class="px-6 py-4 text-sm leading-5 text-gray-500 whitespace-no-wrap border-b border-gray-200">
                                            {{ $ven->total_venta }}</td>
                                        <td
                                            class="px-6 py-4 text-sm leading-5 text-gray-500 whitespace-no-wrap border-b border-gray-200">
                                            {{ $ven->pago }}</td>
                                        <td
                                            class="px-6 py-4 text-sm leading-5 text-gray-500 whitespace-no-wrap border-b border-gray-200">
                                            {{ $ven->forma_de_pago }}</td>
                                        <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200">
                                            <span
                                                class="{{ $ven->saldo != 0.0 ? 'inline-flex px-2 text-xs font-semibold leading-5 text-red-800 bg-red-100 rounded-full' : 'inline-flex px-2 text-xs font-semibold leading-5 text-green-800 bg-green-100 rounded-full' }}">
                                                {{ $ven->saldo }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200">
                                            <span
                                                class="{{ $ven->estado != 'Activo' ? 'inline-flex px-2 text-xs font-semibold leading-5 text-red-800 bg-red-100 rounded-full' : 'inline-flex px-2 text-xs font-semibold leading-5 text-green-800 bg-green-100 rounded-full' }}">
                                                {{ $ven->estado }}
                                            </span>
                                        </td>
                                        <td
                                            class="px-6 py-4 text-sm font-medium leading-5 text-right whitespace-no-wrap border-b border-gray-200">
                                            <button wire:click="ver({{ $ven->id }})"
                                                class="py-2 px-4 shadow-md no-underline rounded-full bg-yellow-600 text-white font-sans font-semibold text-sm border-blue btn-primary hover:text-white hover:bg-blue-light focus:outline-none active:shadow-none mr-2">Ver</button>
                                            @if ($ven->estado === 'Activo')
                                                <button
                                                    wire:click="$emit('confirmarCancelacionVenta',{{ $ven->id }})"
                                                    class="py-2 px-4 shadow-md no-underline rounded-full bg-red-600 text-white font-sans font-semibold text-sm border-blue btn-primary hover:text-white hover:bg-blue-light focus:outline-none active:shadow-none mr-2">Cancelar
                                                    Venta</button>
                                            @endif

                                        </td>

                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @if ($isOpen)
                @include('livewire.list.show-ventas')
            @endif
            <div class="mt-2">
                {{ $ventas->links() }}
            </div>
        </div>
    </div>
    @push('js')
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            Livewire.on('confirmarCancelacionVenta', id => {
                Swal.fire({
                    title: '¿Estás seguro?',
                    text: 'Estás a punto de cancelar esta venta.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, cancelar venta',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Livewire.emit('cancelarVenta', id);
                    }
                });
            });
        </script>
    @endpush
