<div class="fixed inset-0 z-50 overflow-y-auto">
    <div class="flex justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">

        <div class="fixed inset-0 transition-opacity">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full"
            role="dialog" aria-modal="true" aria-labelledby="modal-headline">
            <div
                class="flex  items-center border-l-8 border-emerald-500 bg-emerald-50 p-4 text-emerald-900 shadow-lg mt-6  col-span-12 sm:col-span-12">
                <div class="min-w-0">
                    <h2 class="overflow-hidden text-ellipsis whitespace-nowrap">Abrir Caja</h2>
                </div>
            </div>
            <form>

                <div class="bg-white px-4 pt-5 pb-4 sm:p-4 sm:pb-2 grid grid-cols-12">

                    <div class="mb-4 col-span-12 sm:col-span-6 m-1">
                        <label for="fecha_apertura" class="block text-gray-700 text-sm font-bold mb-2">Fecha de
                            apertura:</label>
                        <input type="date"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            id="fecha_apertura" wire:model="fecha_apertura">
                    </div>

                    <div class="mb-4 col-span-12 sm:col-span-6 m-1">
                        <label for="monto_apertura" class="block text-gray-700 text-sm font-bold mb-2">Monto
                            Apertura:</label>
                        <input type="number"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            id="monto_apertura" wire:model="monto_apertura">

                    </div>
                    @if($isEdit)
                    <div
                        class="flex  items-center border-l-8 border-red-500 bg-rose-50 p-4 text-white-900 shadow-lg mt-6 mb-4 col-span-12 sm:col-span-12">
                        <div class="min-w-0">
                            <h2 class="overflow-hidden text-ellipsis whitespace-nowrap">Cerrar Caja</h2>
                        </div>
                    </div>



                    <div class="mb-4 col-span-12 sm:col-span-4 m-1">
                        <label for="fecha_cierre" class="block text-gray-700 text-sm font-bold mb-2">Fecha de
                            Cierre:</label>
                        <input type="date"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            id="fecha_cierre" wire:model="fecha_cierre">
                    </div>
                    <div class="mb-4 col-span-12 sm:col-span-4 m-1">
                        <label for="monto_cierre_real" class="block text-gray-700 text-sm font-bold mb-2">Monto
                            cierre  en caja real:</label>
                        <input type="number"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            id="monto_cierre_real" wire:model="monto_cierre_real">
                    </div>
                    <div class="mb-4  col-span-12 sm:col-span-4 m-1">
                        <label for="estado" class="block text-gray-700 text-sm font-bold mb-2">Cambiar Estado de
                            Caja:</label>
                        <select wire:model="estado" id="estado" name="estado"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                            <option value="">Selecciona un estado</option>
                            <option value="abierta">Abierta</option>
                            <option value="cerrada">Cerrada</option>
                        </select>
                    </div>
                    <div class="flex  items-center border-l-8 border-gray-600 bg-gray-300 p-4 text-white-900 shadow-lg mt-6 mb-4 col-span-12 sm:col-span-12">
                        <h5 class="text-orange-500 ">Movimientos del dia de hoy</h5>
                    </div>
                    <div class="mb-4 col-span-12 sm:col-span-4 m-1">
                        <label for="monto_total_ventas" class="block text-gray-700 text-sm font-bold mb-2">Monto Total de ventas del dia:</label>
                        <input type="number"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            id="monto_total_ventas" wire:model="monto_total_ventas" disabled>
                    </div>

                    <div class="mb-4 col-span-12 sm:col-span-4 m-1">
                        <label for="monto_cierre" class="block text-gray-700 text-sm font-bold mb-2">Monto
                            cierre en el sistema:</label>
                        <input type="number"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            id="monto_cierre" wire:model="monto_cierre" disabled>
                    </div>

                    <div class="mb-4 col-span-12 sm:col-span-4 m-1">
                        <label for="ventas_efectivo" class="block text-gray-700 text-sm font-bold mb-2">Ventas en efectivo:</label>
                        <input type="number"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            id="ventas_efectivo" wire:model="ventas_efectivo" disabled>
                    </div>

                    <div class="mb-4 col-span-12 sm:col-span-4 m-1">
                        <label for="ventas_transferencia" class="block text-gray-700 text-sm font-bold mb-2">Ventas con transferencia:</label>
                        <input type="number"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            id="ventas_transferencia" wire:model="ventas_transferencia" disabled>
                    </div>

                    <div class="mb-4 col-span-12 sm:col-span-4 m-1">
                        <label for="ventas_tarjeta" class="block text-gray-700 text-sm font-bold mb-2">Ventas en tarjetas:</label>
                        <input type="number"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            id="ventas_tarjeta" wire:model="ventas_tarjeta" disabled>
                    </div>
                    <div class="mb-4 col-span-12 sm:col-span-4 m-1">
                        <label for="ingresos" class="block text-gray-700 text-sm font-bold mb-2">Ingresos:</label>
                        <input type="number"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            id="ingresos" wire:model="ingresos" disabled>
                    </div>
                    <div class="mb-4 col-span-12 sm:col-span-4 m-1">
                        <label for="salidas" class="block text-gray-700 text-sm font-bold mb-2">Salidas:</label>
                        <input type="number"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            id="salidas" wire:model="salidas" disabled>
                    </div>




                    @endif
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-end">
                    <span class="flex w-full rounded-md shadow-sm sm:ml-3 sm:w-auto">
                        <button wire:click.prevent="guardar()" type="button"
                            class="inline-flex justify-center w-full rounded-md border border-transparent px-4 py-2 bg-purple-600 text-base leading-6 font-medium text-white shadow-sm hover:bg-purple-800 focus:outline-none focus:border-green-700 focus:shadow-outline-green transition ease-in-out duration-150 sm:text-sm sm:leading-5">Guardar</button>
                    </span>

                    <span class="flex w-full rounded-md shadow-sm sm:ml-3 sm:w-auto">
                        <button wire:click="closeModal()" type="button"
                            class="inline-flex justify-center w-full rounded-md border border-gray-300 px-4 py-2 bg-gray-200 text-base leading-6 font-medium text-gray-700 shadow-sm hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:shadow-outline-blue transition ease-in-out duration-150 sm:text-sm sm:leading-5">Cancelar</button>
                    </span>
                </div>
            </form>
        </div>


    </div>
</div>
