<div>
    <form>
        <!-- Información del Cliente y Ventas -->
        <div class="p-6 max-w-7xl mx-auto bg-white rounded-lg shadow-md">
            <!-- Encabezado -->
            <h1 class="text-2xl font-bold text-center text-gray-800 mb-6">Gestión de Cuenta de {{ $nombre_cliente }}</h1>

            <!-- Contenedor principal en formato de columnas -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                <!-- Bloque de detalles del pago - Columna 1 -->
                <div class="lg:col-span-1 bg-gray-50 p-4 rounded-lg">
                    <h2 class="text-xl font-semibold text-gray-700 mb-3">Registro de Nuevo Pago</h2>
                    <p class="text-sm text-gray-600 mb-4">
                        Ingrese el monto a aplicar y se distribuirá automáticamente sobre las ventas pendientes.
                    </p>

                    <div class="space-y-4">
                        <div>
                            <label for="monto" class="block text-sm font-medium text-gray-700 mb-1">Monto a
                                Pagar:</label>
                            <input type="number" id="monto" wire:model="monto" wire:change="distribuirPago"
                                class="w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500"
                                placeholder="Ingrese monto" autofocus/>
                        </div>
                        <div>
                            <label for="descripcion"
                                class="block text-sm font-medium text-gray-700 mb-1">Descripción/Referencia
                                (obligatorio):</label>
                            <input type="text" id="descripcion" wire:model="descripcion"
                                class="w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500"
                                placeholder="Ej: Pago de cuenta de tal venta" required minlength="3" maxlength="255" />
                            @error('descripcion')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Resumen de pago -->
                    <div class="bg-purple-50 p-3 rounded-lg my-4 border border-purple-200">
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium">Deuda total:</span>
                            <span class="font-bold text-purple-700">${{ $totalSaldos }}</span>
                        </div>
                        <div class="flex justify-between items-center mt-1">
                            <span class="text-sm font-medium">Monto a pagar:</span>
                            <span class="font-bold text-green-600">${{ $monto ?? '0.00' }}</span>
                        </div>
                        @if ($monto && $monto > 0)
                            <div class="flex justify-between items-center mt-1">
                                <span class="text-sm font-medium">Saldo restante:</span>
                                <span class="font-bold text-gray-800">
                                    ${{ max(0, $totalSaldos - $monto) }}
                                </span>
                            </div>
                        @endif
                    </div>

                    <div class="flex justify-center mt-4">
                        <button wire:click.prevent="guardar"
                            class="bg-purple-600 hover:bg-purple-700 text-white font-medium py-2 px-6 rounded-md transition duration-200 w-full sm:w-auto"
                            wire:loading.attr="disabled" wire:target="guardar">
                            <span wire:loading.remove wire:target="guardar">Registrar Pago</span>
                            <span wire:loading wire:target="guardar">Registrando...</span>
                        </button>
                    </div>

                </div>
                <!-- Lista de ventas con saldo - Sección completa -->
                <div class="lg:col-span-2">
                    <h2 class="text-xl font-semibold mb-4">Historial de Ventas con Saldo Pendiente</h2>
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white border border-gray-200">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="p-2 text-left text-xs font-medium text-gray-600">Fecha</th>
                                    <th class="p-2 text-right text-xs font-medium text-gray-600">Total</th>
                                    <th class="p-2 text-right text-xs font-medium text-gray-600">Saldo actual</th>
                                    <th class="p-2 text-right text-xs font-medium text-gray-600">Saldo nuevo</th>
                                    <th class="p-2 text-center text-xs font-medium text-gray-600">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if (!is_null($ventasConSaldos) && $ventasConSaldos->isNotEmpty())
                                    @foreach ($ventasConSaldos as $venta)
                                        <tr class="hover:bg-gray-50 border-t border-gray-200">
                                            <td class="p-2 text-sm">{{ $venta->created_at->format('d/m/Y') }}</td>
                                            <td class="p-2 text-sm text-right">${{ $venta->total_venta }}</td>
                                            <td class="p-2 text-sm text-right">${{ $venta->saldo }}</td>
                                            <td class="p-2 text-sm text-right">
                                                <input type="number" wire:model="saldos.{{ $venta->id }}"
                                                    class="w-full p-1 text-right border border-gray-300 rounded focus:ring-purple-500 focus:border-purple-500"
                                                    readonly />
                                            </td>
                                            <td class="p-2 text-center">
                                                <button type="button" wire:click="toggleDetail({{ $venta->id }})"
                                                    class="bg-purple-500 hover:bg-purple-600 text-white py-1 px-3 rounded text-sm">
                                                    {{ in_array($venta->id, $detallesVisibles) ? 'Ocultar Detalle' : 'Ver Detalle' }}
                                                </button>
                                            </td>
                                        </tr>

                                        <!-- Detalles expandibles -->
                                        @if (in_array($venta->id, $detallesVisibles))
                                            <tr>
                                                <td colspan="5" class="p-0">
                                                    @include('livewire.ingreso.detallle-venta', [
                                                        'venta' => $venta,
                                                    ])
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="5" class="py-2 px-4 text-center text-gray-500">
                                            No hay ventas con saldo.
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                    <!-- Vista previa de la distribución - Columna 2 y 3 -->
                    <div class="lg:col-span-2 mt-2">
                        @if ($monto && $monto > 0)
                            <div class="bg-yellow-50 p-4 rounded-lg border border-yellow-200 mb-4">
                                <h3 class="font-medium text-yellow-800 mb-2">Vista previa de distribución del pago</h3>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full bg-white border border-gray-200">
                                        <thead>
                                            <tr class="bg-gray-100">
                                                <th class="p-2 text-left text-xs font-medium text-gray-600">Fecha</th>
                                                <th class="p-2 text-left text-xs font-medium text-gray-600">Total</th>
                                                <th class="p-2 text-left text-xs font-medium text-gray-600">Saldo Actual
                                                </th>
                                                <th class="p-2 text-left text-xs font-medium text-gray-600">A Pagar</th>
                                                <th class="p-2 text-left text-xs font-medium text-gray-600">Saldo
                                                    Resultante
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if (!is_null($ventasConSaldos) && $ventasConSaldos->isNotEmpty())
                                                @foreach ($ventasConSaldos as $venta)
                                                    <tr class="border-t border-gray-200">
                                                        <td class="p-2 text-sm">
                                                            {{ $venta->created_at->format('d/m/Y') }}
                                                        </td>
                                                        <td class="p-2 text-sm">${{ $venta->total_venta }}</td>
                                                        <td class="p-2 text-sm">${{ $venta->saldo }}</td>
                                                        <td class="p-2 text-sm text-green-600 font-medium">
                                                            ${{ $venta->saldo - ($saldos[$venta->id] ?? $venta->saldo) }}
                                                        </td>
                                                        <td class="p-2 text-sm font-medium">
                                                            ${{ $saldos[$venta->id] ?? $venta->saldo }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>

        <!-- Botones de Acción -->
        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse mt-4">
            {{-- <span class="flex w-full rounded-md shadow-sm sm:ml-3 sm:w-auto">
                <button wire:click.prevent="guardar" type="button"
                    class="inline-flex justify-center w-full rounded-md border border-transparent px-4 py-2 bg-purple-600 text-base leading-6 font-medium text-white shadow-sm hover:bg-purple-800 focus:outline-none focus:border-purple-700 focus:shadow-outline-purple transition ease-in-out duration-150 sm:text-sm sm:leading-5">Guardar</button>
            </span> --}}
            {{-- <span class="flex w-full rounded-md shadow-sm sm:ml-3 sm:w-auto">
                <button wire:click="closeModal()" type="button"
                    class="inline-flex justify-center w-full rounded-md border border-gray-300 px-4 py-2 bg-gray-200 text-base leading-6 font-medium text-gray-700 shadow-sm hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:shadow-outline-blue transition ease-in-out duration-150 sm:text-sm sm:leading-5">Cancelar</button>
            </span> --}}
        </div>
    </form>
</div>
