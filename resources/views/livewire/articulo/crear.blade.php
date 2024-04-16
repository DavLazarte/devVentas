<div class="fixed z-10 inset-0 overflow-y-auto ease-out duration-400">
    <div class="flex justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">

        <div class="fixed inset-0 transition-opacity">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
            role="dialog" aria-modal="true" aria-labelledby="modal-headline">
            <form>
                <div class="bg-white px-4 pt-5 pb-4 sm:p-4 sm:pb-2">
                    <div class="mb-4">
                        <label for="categoria_id" class="block text-gray-700 text-sm font-bold mb-2">Categoría:</label>
                        <select wire:model="categoria_id" id="categoria_id" name="categoria_id"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            wire:model="categorias">
                            <option value="">Selecciona una categoría</option>
                            @foreach ($categorias as $id => $nombre)
                                {{-- <option value="{{ $id }}">{{ $nombre }}</option> --}}
                                <option value="{{ $id }}" @if ($categoria_id == $id) selected @endif>
                                    {{ $nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    {{-- @if (!$modoEdit)
                        
                    @endif --}}
                    <div class="mb-4">
                        <label for="producto" class="block text-gray-700 text-sm font-bold mb-2">Nombre:</label>
                        <input type="text"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            id="nombre" wire:model="nombre">
                    </div>

                    <div class="mb-4">
                        <label for="descripcion" class="block text-gray-700 text-sm font-bold mb-2">Descripción:</label>
                        <input type="text"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            id="descripcion" wire:model="descripcion">
                    </div>
                    <div class="mb-4">
                        <label for="stock" class="block text-gray-700 text-sm font-bold mb-2">Stock:</label>
                        <input type="number"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            id="stock" wire:model="stock">
                    </div>
                    <div class="mb-4">
                        <label for="precio_unitario" class="block text-gray-700 text-sm font-bold mb-2">Precio
                            Venta</label>
                        <input type="number"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            id="precio_unitario" wire:model="precio_unitario">
                    </div>

                    <div class="mb-4">
                        <label for="estado" class="block text-gray-700 text-sm font-bold mb-2">Estado:</label>
                        <select wire:model="estado" id="estado" name="estado"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                            <option value="">Selecciona un estado</option>
                            <option value="activo">Activo</option>
                            <option value="inactivo">Inactivo</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="codigo" class="block text-gray-700 text-sm font-bold mb-2">Codigo:</label>
                        <input type="number"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            id="codigo" wire:model="codigo">
                    </div>
                    {{-- <div class="mb-4 flex items-center">
                        onchange="generarBarcode()"
                        <div id="print">
                            <svg id="barcode"></svg>
                        </div>
                        <button type="button" class="ml-4 cursor-pointer bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-700" onclick="exportarComoImagen()">
                            Exportar como Imagen
                        </button>
                    </div> --}}


                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <span class="flex w-full rounded-md shadow-sm sm:ml-3 sm:w-auto">
                            <button wire:click.prevent="guardar()" type="button"
                                class="inline-flex justify-center w-full rounded-md border border-transparent px-4 py-2 bg-purple-600 text-base leading-6 font-medium text-white shadow-sm hover:bg-purple-800 focus:outline-none focus:border-green-700 focus:shadow-outline-green transition ease-in-out duration-150 sm:text-sm sm:leading-5">Guardar</button>
                        </span>

                        <span class="flex w-full rounded-md shadow-sm sm:ml-3 sm:w-auto">
                            <button wire:click="closeModal()" type="button"
                                class="inline-flex justify-center w-full rounded-md border border-gray-300 px-4 py-2 bg-gray-200 text-base leading-6 font-medium text-gray-700 shadow-sm hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:shadow-outline-blue transition ease-in-out duration-150 sm:text-sm sm:leading-5">Cancelar</button>
                        </span>
                    </div>

                </div>
            </form>
        </div>


    </div>
</div>
