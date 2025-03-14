<div class="fixed inset-0 z-10 flex items-center justify-center overflow-y-auto bg-gray-500 bg-opacity-75 ease-out duration-400">
    <div class="bg-white rounded-lg shadow-xl transform transition-all sm:max-w-2xl w-full">
        <form>
            <div class="bg-white px-6 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Registrar Persona</h2>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">¿Qué tipo de persona vas a cargar?</label>
                    <div class="flex space-x-4">
                        <label class="inline-flex items-center">
                            <input type="radio" wire:model="tipo_persona" value="cliente" class="form-radio text-purple-600">
                            <span class="ml-2 text-gray-700">Cliente</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" wire:model="tipo_persona" value="proveedor" class="form-radio text-purple-600">
                            <span class="ml-2 text-gray-700">Proveedor</span>
                        </label>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="nombre" class="block text-gray-700 text-sm font-bold mb-2">Nombre:</label>
                    <input type="text" id="nombre" wire:model="nombre" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
                </div>

                <div class="mb-4">
                    <label for="direccion" class="block text-gray-700 text-sm font-bold mb-2">Dirección:</label>
                    <input type="text" id="direccion" wire:model="direccion" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
                </div>

                <div class="mb-4">
                    <label for="telefono" class="block text-gray-700 text-sm font-bold mb-2">Celular:</label>
                    <input type="number" id="telefono" wire:model="telefono" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
                </div>

                <div class="mb-4">
                    <label for="mail" class="block text-gray-700 text-sm font-bold mb-2">Email:</label>
                    <input type="email" id="mail" wire:model="mail" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
                </div>
            </div>

            <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-4">
                <button wire:click.prevent="guardarPersona()" type="button" class="px-4 py-2 bg-purple-600 text-white rounded-lg shadow hover:bg-purple-800 focus:outline-none focus:ring-2 focus:ring-purple-700">Guardar</button>
                <button wire:click="closeModal()" type="button" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg shadow hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-300">Cancelar</button>
            </div>
        </form>
    </div>
</div>
