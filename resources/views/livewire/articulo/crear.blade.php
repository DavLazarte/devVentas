<div class="fixed inset-0 z-50 overflow-hidden">
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-black bg-opacity-50" wire:click="closeModal"></div>
    <!-- Drawer -->
    <div class="absolute inset-y-0 right-0 w-full sm:w-[500px] sm:max-w-lg bg-white shadow-xl max-h-[92vh] flex flex-col">
        <!-- Header -->
        <div class="sticky top-0 bg-white border-b border-gray-200 p-4 flex justify-between items-center z-10">
            <h2 class="text-lg font-semibold text-gray-900">{{ $modoEdit ? 'Editar Artículo' : 'Crear Artículo' }}</h2>
            <button wire:click="closeModal" class="text-gray-500 hover:text-gray-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <!-- Formulario -->
        <form class="flex flex-col h-full" wire:submit.prevent="guardar">
            <div class="flex-1 overflow-y-auto p-4 space-y-4">
                @if (session()->has('error'))
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4" role="alert">
                        <p class="font-bold">ERROR</p>
                        <p>{{ session('error') }}</p>
                    </div>
                @endif
                <div>
                    <label for="categoria_id" class="block text-gray-700 text-sm font-bold mb-1">Categoría</label>
                    <select wire:model="categoria_id" id="categoria_id" name="categoria_id"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 sm:text-sm">
                        <option value="">Selecciona una categoría</option>
                        @foreach ($categorias as $id => $nombre)
                            <option value="{{ $id }}" @if ($categoria_id == $id) selected @endif>
                                {{ $nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="nombre" class="block text-gray-700 text-sm font-bold mb-1">Nombre</label>
                    <input type="text" id="nombre" wire:model="nombre"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 sm:text-sm">
                </div>
                <div>
                    <label for="descripcion" class="block text-gray-700 text-sm font-bold mb-1">Descripción</label>
                    <input type="text" id="descripcion" wire:model="descripcion"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 sm:text-sm">
                </div>
                <div>
                    <label for="codigo" class="block text-gray-700 text-sm font-bold mb-1">Código</label>
                    <input type="text" id="codigo" wire:model="codigo"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 sm:text-sm">
                </div>
                <div>
                    <label for="imagen" class="block text-gray-700 text-sm font-bold mb-1">Imagen del producto</label>
                    <input type="file" id="imagen" wire:model="imagen"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 sm:text-sm">
                    @error('imagen')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                    <span wire:loading wire:target="imagen" class="text-sm text-gray-600">Cargando imagen...</span>
                    @if ($imagen_actual)
                        <div class="mt-2">
                            <label class="block text-sm font-medium text-gray-700">Imagen Actual:</label>
                            <img src="{{ asset('storage/' . $imagen_actual) }}"
                                class="w-24 h-24 object-cover mt-1 rounded-md border">
                        </div>
                    @endif
                </div>
                <div class="flex space-x-2">
                    <div class="flex-1">
                        <label for="stock" class="block text-gray-700 text-sm font-bold mb-1">Stock</label>
                        <input type="number" id="stock" wire:model="stock"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 sm:text-sm">
                    </div>
                    <div class="flex-1">
                        <label for="precio_unitario" class="block text-gray-700 text-sm font-bold mb-1">Precio Venta</label>
                        <input type="number" id="precio_unitario" wire:model="precio_unitario" step="0.01"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 sm:text-sm">
                    </div>
                </div>
                {{-- <div>
                    <label for="estado" class="block text-gray-700 text-sm font-bold mb-2">Estado:</label>
                    <div class="flex items-center">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" id="estado" wire:model="estado"  name="estado" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-purple-600"></div>
                            <span class="ml-3 text-sm font-medium text-gray-700">{{ $estado == 'activo' ? 'Activo' : 'Inactivo' }}</span>
                        </label>
                    </div>
                </div> --}}
                <div>
                    <label for="estado" class="block text-gray-700 text-sm font-bold mb-2">Disponible:</label>
                    <div class="flex items-center">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" id="estado" class="sr-only peer"
                                {{ $estado === 'activo' ? 'checked' : '' }}
                                wire:click="$set('estado', $estado === 'activo' ? 'inactivo' : 'activo')">
                            <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-purple-600"></div>
                            <span class="ml-3 text-sm font-medium text-gray-700">desactivar si no cuenta con stock</span>
                        </label>
                    </div>
                </div>
                <div>
                    <label for="destacado" class="block text-gray-700 text-sm font-bold mb-2">Destacado:</label>
                    <div class="flex items-center">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" id="destacado" wire:model="destacado" name="destacado" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-purple-600"></div>
                            <span class="ml-3 text-sm font-medium text-gray-700">{{ $destacado == 1 ? 'Si' : 'No' }}</span>
                        </label>
                    </div>
                </div>
                <div>
                    <label for="mostrar_feed" class="block text-gray-700 text-sm font-bold mb-2">Catalogo:</label>
                    <div class="flex items-center">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" id="mostrar_feed" wire:model="mostrar_feed" name="mostrar_feed" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-purple-600"></div>
                            <span class="ml-3 text-sm font-medium text-gray-700">{{ $mostrar_feed == 1 ? 'Si' : 'No' }}</span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="bg-white border-t border-gray-200 p-4 flex space-x-2 shrink-0">
                <button wire:click.prevent="guardar()" type="button"
                    class="flex-1 inline-flex justify-center rounded-md border border-transparent px-4 py-2 bg-purple-600 text-base leading-6 font-medium text-white shadow-sm hover:bg-purple-800 focus:outline-none focus:border-purple-700 focus:shadow-outline-purple transition ease-in-out duration-150 sm:text-sm sm:leading-5"
                    wire:loading.attr="disabled" wire:loading.class="opacity-50"
                    wire:target="guardar, imagen">
                    <span wire:loading.remove wire:target="guardar">Guardar</span>
                    <span wire:loading wire:target="guardar">Guardando...</span>
                </button>
                <button wire:click="closeModal()" type="button"
                    class="flex-1 inline-flex justify-center rounded-md border border-gray-300 px-4 py-2 bg-gray-200 text-base leading-6 font-medium text-gray-700 shadow-sm hover:text-gray-500 focus:outline-none focus:border-gray-300 focus:shadow-outline-gray transition ease-in-out duration-150 sm:text-sm sm:leading-5">
                    Cancelar
                </button>
            </div>
        </form>
    </div>
</div>
