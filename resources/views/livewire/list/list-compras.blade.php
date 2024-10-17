    <x-slot name="header">
    </x-slot>
    <div class="py-12">
        <div class="max-w-12xl mx-auto sm:px6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg px-4 py-4">
                <h1 class="text-gray-900">Listado de Compras</h1>


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
                                            Tipo de Compra</th>
                                        <th
                                            class="px-6 py-3 text-xs font-medium leading-4 tracking-wider text-left text-gray-500 uppercase border-b border-gray-200 bg-gray-50">
                                            Total Compra</th>
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
                                            class="px-6 py-3 text-xs font-medium leading-4 tracking-wider text-left text-gray-500 uppercase border-b border-gray-200 bg-gray-50">
                                            Acciones</th>

                                    </tr>
                                </thead>

                                <tbody class="bg-white">
                                    @foreach ($compras as $ven)
                                        <tr>
                                            <td
                                                class="px-6 py-4 text-sm leading-5 text-gray-500 whitespace-no-wrap border-b border-gray-200">
                                                {{ $ven->id }}</td>
                                            <td
                                                class="px-6 py-4 text-sm leading-5 text-gray-500 whitespace-no-wrap border-b border-gray-200">
                                                {{ $ven->created_at->format('Y-m-d') }}</td>
                                            <td
                                                class="px-6 py-4 text-sm leading-5 text-gray-500 whitespace-no-wrap border-b border-gray-200">
                                                {{ optional($ven->proveedor)->nombre ?? 'Compra Rapida' }}</td>
                                            <td
                                                class="px-6 py-4 text-sm leading-5 text-gray-500 whitespace-no-wrap border-b border-gray-200">
                                                {{ $ven->tipo_compra }}</td>
                                            <td
                                                class="px-6 py-4 text-sm leading-5 text-gray-500 whitespace-no-wrap border-b border-gray-200">
                                                {{ $ven->total }}</td>
                                            <td
                                                class="px-6 py-4 text-sm leading-5 text-gray-500 whitespace-no-wrap border-b border-gray-200">
                                                {{ number_format(round($ven->pago, 2), 2) }}</td>
                                            <td
                                                class="px-6 py-4 text-sm leading-5 text-gray-500 whitespace-no-wrap border-b border-gray-200">
                                                {{ $ven->tipo_pago }}</td>
                                            <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200">
                                                <span
                                                    class="{{ $ven->saldo != 0.0 ? 'inline-flex px-2 text-xs font-semibold leading-5 text-red-800 bg-red-100 rounded-full' : 'inline-flex px-2 text-xs font-semibold leading-5 text-green-800 bg-green-100 rounded-full' }}">
                                                    {{ number_format(round($ven->saldo, 2), 2) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200">
                                                <span
                                                    class="{{ $ven->estado != 'activo' ? 'inline-flex px-2 text-xs font-semibold leading-5 text-red-800 bg-red-100 rounded-full' : 'inline-flex px-2 text-xs font-semibold leading-5 text-green-800 bg-green-100 rounded-full' }}">
                                                    {{ $ven->estado }}
                                                </span>
                                            </td>
                                            <td
                                                class="px-6 py-4 text-sm font-medium leading-5 text-right whitespace-no-wrap border-b border-gray-200">
                                                <div class="inline-flex space-x-2">
                                                    <!-- Botón "Ver" -->
                                                    <button wire:click="ver({{ $ven->id }})"
                                                        class="inline-flex items-center py-2 px-4 shadow-md no-underline rounded-full bg-yellow-600 text-white font-sans font-semibold text-sm border-blue btn-primary hover:text-white hover:bg-yellow-700 focus:outline-none active:shadow-none">
                                                        <i class="fas fa-eye"></i>
                                                    </button>

                                                    <!-- Botón "Cancelar Compra" si la compra está activa -->
                                                    @if ($ven->estado === 'activo')
                                                        <button
                                                            wire:click="$emit('confirmarCancelacionCompra', {{ $ven->id }})"
                                                            class="inline-flex items-center py-2 px-4 shadow-md no-underline rounded-full bg-red-600 text-white font-sans font-semibold text-sm border-blue btn-primary hover:text-white hover:bg-red-700 focus:outline-none active:shadow-none">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>




                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @if ($isOpen)
                    @include('livewire.list.show-compras')
                @endif

                <div class="mt-2">
                    {{ $compras->links() }}
                </div>
            </div>
        </div>
        @push('js')
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
            <script>
                Livewire.on('confirmarCancelacionCompra', id => {
                    Swal.fire({
                        title: '¿Estás seguro?',
                        text: 'Estás a punto de cancelar esta compra.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Sí, cancelar compra',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            Livewire.emit('cancelarCompra', id);
                        }
                    });
                });
            </script>
        @endpush
