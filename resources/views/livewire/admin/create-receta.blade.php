<!-- Modal para crear receta -->
<div class="fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen">
        <div class="bg-white rounded-lg shadow-xl p-6 sm:p-10 w-full max-w-8lg">
            <!-- Sección 1: Inputs para la receta -->
            <div class="mb-6">
                <h2 class="text-2xl font-bold mb-4">Crear nueva receta</h2>
                <form>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="nombre" class="block mb-1">Nombre de la receta</label>
                            <input type="text" id="nombre" wire:model="nombre"
                                class="w-full border rounded-md px-3 py-2">
                        </div>
                        <div>
                            <label for="descripcion" class="block mb-1">Descripción</label>
                            <input id="descripcion" wire:model="descripcion" class="w-full border rounded-md px-3 py-2">
                        </div>
                    </div>
                </form>
            </div>

            <!-- ... (código anterior) -->

            <!-- Sección 2: Tabla dinámica -->
            <div>
                <h2 class="text-2xl font-bold mb-4">Materia Prima</h2>
                <div class="flex flex-wrap">
                    <!-- Grid de inputs para los cálculos -->
                    <div class="w-full sm:w-1/2 md:w-2/3 lg:w-3/4 xl:w-4/5 mt-4 px-2">
                        <div class="grid grid-cols-4 gap-4">
                            <!-- Select para seleccionar materia prima -->
                            <div class="relative">
                                <input wire:model.debounce.300ms="searchMateriaPrima" type="text" class="block w-full border border-gray-300 rounded-md py-2 px-3 text-black" placeholder="Buscar materia prima">

                                @if($searchMateriaPrima)
                                    <div class="absolute z-50 bg-white w-full mt-1 rounded-md shadow-lg">
                                        @foreach($materiaPrima as $opcion)
                                            <div wire:click="agregarMateriaPrima({{ $opcion['id'] }})" class="py-2 px-3 cursor-pointer hover:bg-gray-100">{{ $opcion['producto'] }}</div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            {{-- <div>
                                <select wire:model="nuevaMateriaPrima"
                                    class="block w-full border border-gray-300 rounded-md py-2 px-3 text-black">
                                    <option value="">Seleccionar materia prima</option>
                                    <!-- Iterar sobre las opciones de materia prima disponibles -->
                                    @foreach ($materiaPrima as $opcion)
                                        <option value="{{ $opcion['id'] }}">{{ $opcion['producto'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <button wire:click="agregarMateriaPrima"
                                    class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-md">Agregar</button>
                            </div> --}}
                        </div>
                    </div>
                </div>


                <table class="w-full border-collapse border border-gray-200 mt-3">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="border border-gray-200 px-4 py-2">Ingredientes</th>
                            <th class="border border-gray-200 px-4 py-2">Peso kg|L</th>
                            <th class="border border-gray-200 px-4 py-2">Costo</th>
                            <th class="border border-gray-200 px-4 py-2">Stock en kg-l</th>
                            <th class="border border-gray-200 px-4 py-2">Cantidad utilizado / kilo</th>
                            <th class="border border-gray-200 px-4 py-2">Costo en receta / $</th>
                            <!-- Otros encabezados según tu estructura -->
                            <th class="border border-gray-200 px-4 py-2">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>

                        <!-- Iteración sobre la lista de materias prima seleccionada -->
                        @foreach ($materiaPrimaSeleccionada as $index => $materia)
                            @if (!isset($materia['eliminado']) || !$materia['eliminado'])
                                <tr>
                                    <td>
                                        <input type="text" id="producto_{{ $index }}" wire:model="id_materia"
                                             value="{{ isset($materia['id']) ? $materia['id'] : '' }}"
                                            placeholder="{{ isset($materia['producto']) ? $materia['producto'] : '' }}">
                                    </td>

                                    <td class="border border-gray-200 px-4 py-2">
                                        <input type="number" id="peso_{{ $index }}"
                                            value="{{ isset($materia['peso']) ? $materia['peso'] : '' }}">
                                    </td>
                                    <td class="border border-gray-200 px-4 py-2">
                                        <input type="number" id="precio_{{ $index }}"
                                            value="{{ isset($materia['precio']) ? $materia['precio'] : '' }}">
                                    </td>
                                    <td class="border border-gray-200 px-4 py-2">
                                        <input type="number" id="stock_{{ $index }}"
                                            value="{{ isset($materia['stock']) ? $materia['stock'] : '' }}">
                                    </td>
                                    <!-- Resto de las celdas -->
                                    {{-- <td class="border border-gray-200 px-4 py-2">
                                        <input type="number" id="cantidad_{{ $index }}" wire:model="cantidad">
                                    </td>
                                    <td class="border border-gray-200 px-4 py-2">
                                        <input type="number" id="costo_en_receta_{{ $index }}"
                                            wire:model="costo_en_receta">
                                    </td> --}}
                                    <td class="border border-gray-200 px-4 py-2">
                                        <input type="number" id="cantidad_{{ $index }}"
                                            wire:model="materiaPrimaSeleccionada.{{ $index }}.cantidad"
                                            wire:change="calcularCostoEnReceta({{ $index }})">
                                    </td>

                                    <td class="border border-gray-200 px-4 py-2">
                                        <input type="number" id="costo_en_receta_{{ $index }}"
                                            wire:model="materiaPrimaSeleccionada.{{ $index }}.costo_en_receta"
                                              wire:change="actualizarCostoEnReceta({{ $index }})">
                                    </td>

                                    <td class="border border-gray-200 px-4 py-2">
                                        <button wire:click="eliminarMateriaPrima({{ $index }})"
                                            class="text-red-600 hover:text-red-800">Eliminar</button>
                                    </td>
                                </tr>
                            @endif
                        @endforeach

                    </tbody>
                </table>
            </div>

            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <div>
                    <label for="sub_total_costo_elaboracion" class="block">SUB TOTAL COSTO DE ELABORACIÓN</label>
                    <input type="number" id="sub_total_costo_elaboracion" wire:model="subTotalCostoElaboracion"
                        class="w-full border border-gray-300 rounded-md py-2 px-3 text-black"  >
                </div>
            </div>
            <h2 class="text-2xl font-bold mb-4">Calculos Finales</h2>
            <div class="flex flex-wrap">
                <!-- Grid de inputs para los cálculos -->
                <div class="w-full sm:w-1/2 md:w-2/3 lg:w-3/4 xl:w-4/5 mt-4 px-2">
                    <div class="grid grid-cols-5 gap-4">

                        <div>
                            <label for="empleado" class="block">EMPLEADO</label>
                            <input type="number" id="empleado" wire:model="empleado"
                                class="w-full border border-gray-300 rounded-md py-2 px-3 text-black">
                        </div>
                        <div>
                            <label for="costo_elaboracion" class="block">COSTO DE ELABORACIÓN</label>
                            <input type="number" id="costo_elaboracion" wire:model="costo_elaboracion"
                                class="w-full border border-gray-300 rounded-md py-2 px-3 text-black"  >
                        </div>
                        <div>
                            <label for="porciones" class="block ">PORCIONES</label>
                            <input type="number" id="porciones" wire:model="porciones"
                                class="w-full border border-gray-300 rounded-md py-2 px-3 text-black">
                        </div>
                        <div>
                            <label for="costo_unitario" class="block">COSTO UNITARIO</label>
                            <input type="number" id="costo_unitario" wire:model="costo_unitario"
                                class="w-full border border-gray-300 rounded-md py-2 px-3 text-black">
                        </div>
                        <div>
                            <label for="porcentaje_ganancia" class="block">% DE GANANCIA</label>
                            <input type="number" id="porcentaje_ganancia" wire:model="porcentaje_ganancia"
                                class="w-full border border-gray-300 rounded-md py-2 px-3 text-black" wire:change="calcularPorcentaje">
                        </div>
                        <div>
                            <label for="precio_unitario" class="block">PRECIO</label>
                            <input type="number" id="precio_unitario" wire:model="precio_unitario"
                                class="w-full border border-gray-300 rounded-md py-2 px-3 text-black"  >
                        </div>
                        <div>
                            <label for="iva" class="block">IVA</label>
                            <input type="number" id="iva" wire:model="iva"
                                class="w-full border border-gray-300 rounded-md py-2 px-3 text-black" wire:change="calcularIva">
                        </div>
                        <div>
                            <label for="precio_iva" class="block">PRECIO + IVA</label>
                            <input type="number" id="precio_iva" wire:model="precio_iva"
                                class="w-full border border-gray-300 rounded-md py-2 px-3 text-black" >
                        </div>
                        <div>
                            <label for="ganancia" class="block">GANANCIA</label>
                            <input type="number" id="ganancia" wire:model="ganancia"
                                class="w-full border border-gray-300 rounded-md py-2 px-3 text-black">
                        </div>
                        <div>
                            <span class="flex w-full rounded-md shadow-sm sm:ml-3 sm:w-auto">
                                <button wire:click.prevent="guardar()" type="button"
                                    class="inline-flex justify-center w-full rounded-md border border-transparent px-4 py-2 bg-purple-600 text-base leading-6 font-medium text-white shadow-sm hover:bg-purple-800 focus:outline-none focus:border-green-700 focus:shadow-outline-green transition ease-in-out duration-150 sm:text-sm sm:leading-5">Guardar</button>
                            </span>
                            <span class="flex w-full rounded-md shadow-sm sm:ml-3 sm:w-auto">
                                <button wire:click="cerrarModal()" type="button"
                                    class="inline-flex justify-center w-full rounded-md border border-gray-300 px-4 py-2 bg-gray-200 text-base leading-6 font-medium text-gray-700 shadow-sm hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:shadow-outline-blue transition ease-in-out duration-150 sm:text-sm sm:leading-5">Cancelar</button>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>

<!-- ... (otras secciones de tu vista) -->
