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

            {{-- Pasar la fecha al componente de la tabla --}}
            @livewire('list.list-ventas-table', ['fecha' => $fecha])

            @if ($isLoading)
                @livewire('loading-indicator')
            @endif
            @if ($isOpen)
                @include('livewire.list.show-ventas')
            @endif
           
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
                    input: 'text', // Esto permite que el usuario ingrese el motivo
                    inputPlaceholder: 'Ingresa el motivo de cancelación...',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, cancelar venta',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Pasar el id y el motivo ingresado
                        Livewire.emit('cancelarVenta', id, result.value);
                    }
                });
            });
        </script>
    @endpush
