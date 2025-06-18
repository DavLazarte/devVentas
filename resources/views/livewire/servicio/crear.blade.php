<div class="fixed z-10 inset-0 overflow-y-auto ease-out duration-400">
    <div class="flex justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>&#8203;
        @if (session()->has('error'))
            <div class="mt-4 bg-purple-100 border-l-4 border-red-500 text-red-700 p-4" role="alert">
                <p class="font-bold">ERROR</p>
                <p>{{ session('error') }}</p>
            </div>
        @endif

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle max-w-full sm:max-w-md w-full"
            role="dialog" aria-modal="true" aria-labelledby="modal-headline">
            <form>
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="mb-4">
                        <label for="categoria_id" class="block text-gray-700 text-sm font-bold mb-2">Categoría:</label>
                        <select wire:model="categoria_id" id="categoria_id" name="categoria_id"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                            <option value="">Selecciona una categoría</option>
                            @foreach ($categorias as $id => $nombre)
                                <option value="{{ $id }}" @if ($categoria_id == $id) selected @endif>
                                    {{ $nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-4">
                        <label for="nombre" class="block text-gray-700 text-sm font-bold mb-2">Nombre:</label>
                        <input type="text" id="nombre" wire:model="nombre"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>


                    <div class="mb-4">
                        <label for="descripcion" class="block text-gray-700 text-sm font-bold mb-2">Descripción:</label>
                        <input type="text" id="descripcion" wire:model="descripcion"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>
                    <div>
                        <label for="imagen" class="block text-sm font-medium text-gray-700">Imagen para el servicio :</label>
                        <input type="file" id="imagen" wire:model="imagen"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        @error('imagen')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror

                        <!-- Mensaje de carga mientras se sube la imagen -->
                        <span wire:loading wire:target="imagen" class="text-sm text-gray-600">Cargando imagen...</span>
                    </div>

                    <!-- Vista previa de la imagen subida -->
                    @if ($imagen_actual)
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Imagen Actual:</label>
                            <img src="{{ asset('storage/' . $imagen_actual) }}" class="w-32 h-32 object-cover mt-2">
                        </div>
                    @endif
                
                    <div class="mb-4">
                        <label for="precio" class="block text-gray-700 text-sm font-bold mb-2">Precio</label>
                        <input type="number" id="precio" wire:model="precio"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>
                    <div class="mb-4">
                        <label for="duracion" class="block text-gray-700 text-sm font-bold mb-2">duración:</label>
                        <input type="text" id="duracion" wire:model="duracion"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>

                    <div class="mb-4">
                        <label for="estado" class="block text-gray-700 text-sm font-bold mb-2">Estado:</label>
                        <div class="flex items-center">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" id="estado" wire:model="estado"  name="estado" class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-purple-600"></div>
                                <span class="ml-3 text-sm font-medium text-gray-700">{{ $estado == 1 ? 'Activo' : 'Inactivo' }}</span>
                            </label>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label for="estado" class="block text-gray-700 text-sm font-bold mb-2">Destacado:</label>
                        <div class="flex items-center">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" id="destacado" wire:model="destacado" name="destacado" class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-purple-600"></div>
                                <span class="ml-3 text-sm font-medium text-gray-700">{{ $destacado == 1 ? 'Si' : 'No' }}</span>
                            </label>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label for="estado" class="block text-gray-700 text-sm font-bold mb-2">Mostrar en feed:</label>
                        <div class="flex items-center">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" id="mostrar_feed" wire:model="mostrar_feed" name="mostrar_feed" class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-purple-600"></div>
                                <span class="ml-3 text-sm font-medium text-gray-700">{{ $mostrar_feed == 1 ? 'Si' : 'No' }}</span>
                            </label>
                        </div>
                    </div>

                    <div class="bg-gray-50 px-4 py-3 flex justify-end space-x-4">
                        <span class="flex w-full rounded-md shadow-sm sm:ml-3 sm:w-auto mt-3 sm:mt-0">
                            <button wire:click.prevent="guardar()" type="button"
                                class="inline-flex justify-center w-full rounded-md border border-transparent px-4 py-2 bg-purple-600 text-base leading-6 font-medium text-white shadow-sm hover:bg-purple-800 focus:outline-none focus:border-purple-700 focus:shadow-outline-purple transition ease-in-out duration-150 sm:text-sm sm:leading-5"
                                wire:loading.attr="disabled" wire:loading.class="opacity-50" wire:target="guardar, imagen">
                                <span wire:loading.remove wire:target="guardar">Guardar</span>
                                <span wire:loading wire:target="guardar">Guardando...</span>
                            </button>
                        </span>

                        <span class="flex w-full rounded-md shadow-sm sm:ml-3 sm:w-auto mt-3 sm:mt-0">
                            <button wire:click="closeModal()" type="button"
                                class="inline-flex justify-center w-full rounded-md border border-gray-300 px-4 py-2 bg-gray-200 text-base leading-6 font-medium text-gray-700 shadow-sm hover:text-gray-500 focus:outline-none focus:border-gray-300 focus:shadow-outline-gray transition ease-in-out duration-150 sm:text-sm sm:leading-5">
                                Cancelar
                            </button>
                        </span>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
