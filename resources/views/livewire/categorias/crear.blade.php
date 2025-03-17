<div class="fixed inset-0 z-10 overflow-y-auto ease-out duration-400">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md w-full" role="dialog" aria-modal="true" aria-labelledby="modal-headline">
            <form>
                <div class="px-4 pt-5 pb-4 bg-white sm:p-6 sm:pb-4">
                    <div class="mb-4">
                        <label for="nombre" class="block text-sm font-medium text-gray-700">Nombre:</label>
                        <input type="text" id="nombre" wire:model="nombre" class="block w-full px-3 py-2 mt-1 border rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500 sm:text-sm border-gray-300">
                    </div>

                    <div class="mb-4">
                        <label for="descripcion" class="block text-sm font-medium text-gray-700">Descripción:</label>
                        <input type="text" id="descripcion" wire:model="descripcion" class="block w-full px-3 py-2 mt-1 border rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500 sm:text-sm border-gray-300">
                    </div>

                    <div class="mb-4">
                        <label for="estado" class="block text-sm font-medium text-gray-700">Estado:</label>
                        <select id="estado" wire:model="estado" class="block w-full px-3 py-2 mt-1 border rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500 sm:text-sm border-gray-300">
                            <option value="">Selecciona un estado</option>
                            <option value="activo">Activo</option>
                            <option value="inactivo">Inactivo</option>
                        </select>
                    </div>
                </div>

                <div class="px-4 py-3 bg-gray-50 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button wire:click.prevent="guardar()" type="button" class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white bg-purple-600 border border-transparent rounded-md shadow-sm hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 sm:w-auto sm:text-sm">Guardar</button>
                    <button wire:click="closeModal()" type="button" class="inline-flex justify-center w-full px-4 py-2 mt-3 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 sm:mt-0 sm:w-auto sm:text-sm sm:ml-3">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>
