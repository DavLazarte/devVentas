<div class="fixed inset-0 z-10 overflow-y-auto ease-out duration-400">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md w-full"
            role="dialog" aria-modal="true" aria-labelledby="modal-headline">
            <form wire:submit.prevent="guardar">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    @if (session()->has('error'))
                    <div class="mt-4 bg-purple-100 border-l-4 border-red-500 text-red-700 p-4" role="alert">
                        <p class="font-bold">Error</p>
                        <p>{{ session('error') }}</p>
                    </div>
                @endif
                    <h2 class="text-lg font-semibold text-gray-900">Nueva Categoría</h2>

                        <div class="mb-4">
                            <label for="nombre" class="block text-sm font-medium text-gray-700">Nombre:</label>
                            <input type="text" id="nombre" wire:model="nombre"
                                class="block w-full px-3 py-2 mt-1 border rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500 sm:text-sm border-gray-300">
                            @error('nombre')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="descripcion"
                                class="block text-sm font-medium text-gray-700">Descripción:</label>
                            <input type="text" id="descripcion" wire:model="descripcion"
                                class="block w-full px-3 py-2 mt-1 border rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500 sm:text-sm border-gray-300">
                            @error('descripcion')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Estado</label>
                            <div class="flex space-x-4">
                                <label class="inline-flex items-center">
                                    <input type="radio" wire:model="estado" value="activo"
                                        class="form-radio text-purple-600">
                                    <span class="ml-2 text-gray-700">Activo</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" wire:model="estado" value="inactivo"
                                        class="form-radio text-purple-600">
                                    <span class="ml-2 text-gray-700">Inactivo</span>
                                </label>
                            </div>
                            @error('estado')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                </div>

                <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-4">
                    <button type="submit"
                        class="px-4 py-2 bg-purple-600 text-white rounded-lg shadow hover:bg-purple-800 focus:outline-none focus:ring-2 focus:ring-purple-700"
                        wire:loading.attr="disabled" wire:target="guardar">
                        <span wire:loading.remove wire:target="guardar">Guardar</span>
                        <span wire:loading wire:target="guardar">Guardando...</span>
                    </button>
                    <button wire:click="closeModal()" type="button"
                        class="inline-flex justify-center w-full px-4 py-2 mt-3 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 sm:mt-0 sm:w-auto sm:text-sm sm:ml-3">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>
