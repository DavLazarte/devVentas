<div class="fixed z-10 inset-0 overflow-y-auto ease-out duration-400">
    <div class="flex justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>&#8203;

        <div class="inline-block  bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle max-w-full sm:max-w-md w-full"
            role="dialog" aria-modal="true" aria-labelledby="modal-headline">
            <form>
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h2 class="text-lg font-semibold text-gray-900">Registrar Persona</h2>

                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">¿Qué tipo de persona vas a
                            cargar?</label>
                        <div class="flex space-x-4">
                            <label class="inline-flex items-center">
                                <input type="radio" wire:model="tipo_persona" value="cliente"
                                    class="form-radio text-purple-600">
                                <span class="ml-2 text-gray-700">Cliente</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" wire:model="tipo_persona" value="proveedor"
                                    class="form-radio text-purple-600">
                                <span class="ml-2 text-gray-700">Proveedor</span>
                            </label>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="nombre" class="block text-gray-700 text-sm font-bold mb-2">Nombre:</label>
                        <input type="text" id="nombre" wire:model="nombre"
                            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
                    </div>
                    <div class="mb-4">
                        <label for="dni_cuit" class="block text-gray-700 text-sm font-bold mb-2">DNI/CUIT:</label>
                        <input type="text" id="dni_cuit" wire:model="dni_cuit"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>

                    <div class="mb-4">
                        <label for="direccion" class="block text-gray-700 text-sm font-bold mb-2">Dirección:</label>
                        <input type="text" id="direccion" wire:model="direccion"
                            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
                    </div>

                    <div class="mb-4">
                        <label for="telefono" class="block text-gray-700 text-sm font-bold mb-2">Celular:</label>
                        <input type="number" id="telefono" wire:model="telefono"
                            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
                    </div>

                    <div class="mb-4">
                        <label for="mail" class="block text-gray-700 text-sm font-bold mb-2">Email:</label>
                        <input type="email" id="mail" wire:model="mail"
                            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
                    </div>
                </div>

                <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-4">
                    <button wire:click.prevent="guardarPersona()" type="button"
                        class="px-4 py-2 bg-purple-600 text-white rounded-lg shadow hover:bg-purple-800 focus:outline-none focus:ring-2 focus:ring-purple-700">Guardar</button>
                    <button wire:click="closeModal()" type="button"
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg shadow hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-300">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>
