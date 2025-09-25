<div class="fixed inset-0 z-50 overflow-hidden">
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-black bg-opacity-50" wire:click="closeModal"></div>

    <!-- Modal -->
    <div class="absolute inset-y-0 right-0 w-full sm:w-[800px] sm:max-w-4xl bg-white shadow-xl flex flex-col">
        <!-- Header -->
        <div class="sticky top-0 bg-white border-b border-gray-200 p-6 flex justify-between items-center z-10">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">{{ $modoEdit ? 'Editar Artículo' : 'Crear Artículo' }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    {{ $tiene_variantes ? 'Producto con variantes (color, talla, etc.)' : 'Producto simple' }}
                </p>
            </div>
            <button wire:click="closeModal" class="text-gray-500 hover:text-gray-700 p-2">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Formulario -->
        <form class="flex flex-col h-full overflow-hidden" wire:submit.prevent="guardar">
            <div class="flex-1 overflow-y-auto p-6 space-y-6 min-h-0">

                @if (session()->has('error'))
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded" role="alert">
                        <p class="font-bold">ERROR</p>
                        <p>{{ session('error') }}</p>
                    </div>
                @endif

                <!-- SECCIÓN 1: INFORMACIÓN BÁSICA -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Información Básica</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Categoría -->
                        <div>
                            <label for="categoria_id" class="block text-sm font-medium text-gray-700 mb-2">Categoría
                                *</label>
                            <select wire:model="categoria_id" id="categoria_id"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                                <option value="">Selecciona una categoría</option>
                                @foreach ($categorias as $id => $nombre)
                                    <option value="{{ $id }}">{{ $nombre }}</option>
                                @endforeach
                            </select>
                            @error('categoria_id')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Código -->
                        <div>
                            <label for="codigo" class="block text-sm font-medium text-gray-700 mb-2">Código</label>
                            <input type="text" id="codigo" wire:model="codigo"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500"
                                placeholder="Ej: PROD-001">
                            @error('codigo')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <!-- Nombre -->
                    <div class="mt-4">
                        <label for="nombre" class="block text-sm font-medium text-gray-700 mb-2">Nombre del Producto
                            *</label>
                        <input type="text" id="nombre" wire:model="nombre"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500"
                            placeholder="Ej: Camiseta Básica">
                        @error('nombre')
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Descripción -->
                    <div class="mt-4">
                        <label for="descripcion" class="block text-sm font-medium text-gray-700 mb-2">Descripción
                            *</label>
                        <textarea id="descripcion" wire:model="descripcion" rows="3"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500"
                            placeholder="Descripción del producto..."></textarea>
                        @error('descripcion')
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- SECCIÓN 2: CONFIGURACIÓN DE PRODUCTO -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Configuración de Producto</h3>

                    <!-- Toggle Variantes -->
                    <div class="mb-6 p-4 bg-white rounded-lg border-2 border-dashed border-gray-300">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="text-md font-medium text-gray-800">¿Este producto tiene variantes?</h4>
                                <p class="text-sm text-gray-500 mt-1">
                                    Las variantes permiten diferentes opciones como color, talla, capacidad, etc.
                                </p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" wire:model.live="tiene_variantes" class="sr-only peer">
                                <div
                                    class="w-14 h-8 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-7 after:w-7 after:transition-all peer-checked:bg-purple-600">
                                </div>
                            </label>
                        </div>

                        @if ($tiene_variantes)
                            <div class="mt-3 p-3 bg-purple-50 rounded border-l-4 border-purple-400">
                                <p class="text-sm text-purple-700">
                                    ✓ Producto con variantes activado. Los precios y stock se configurarán por cada
                                    variante.
                                </p>
                            </div>
                        @else
                            <div class="mt-3 p-3 bg-blue-50 rounded border-l-4 border-blue-400">
                                <p class="text-sm text-blue-700">
                                    ✓ Producto simple activado. Se usará un solo precio y stock.
                                </p>
                            </div>
                        @endif
                    </div>

                    <!-- Campos condicionales -->
                    @if (!$tiene_variantes)
                        <!-- Precio y Stock para productos simples -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="precio_unitario" class="block text-sm font-medium text-gray-700 mb-2">Precio
                                    de Venta *</label>
                                <div class="relative">
                                    <span
                                        class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">$</span>
                                    <input type="number" id="precio_unitario" wire:model="precio_unitario"
                                        step="0.01"
                                        class="block w-full pl-8 rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500"
                                        placeholder="0.00">
                                </div>
                                @error('precio_unitario')
                                    <span class="text-red-500 text-xs">{{ $message }}</span>
                                @enderror
                            </div>
                            <div>
                                <label for="stock" class="block text-sm font-medium text-gray-700 mb-2">Stock Inicial
                                    *</label>
                                <input type="number" id="stock" wire:model="stock" min="0"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500"
                                    placeholder="0">
                                @error('stock')
                                    <span class="text-red-500 text-xs">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    @endif
                </div>

                <!-- SECCIÓN 3: VARIANTES (solo si está activado) -->
                @if ($tiene_variantes)
                    <div class="bg-gradient-to-r from-purple-50 to-indigo-50 p-4 rounded-lg border border-purple-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">⚙️ Configuración de Variantes</h3>

                        <!-- Selección de Atributos -->
                        <div class="bg-white p-4 rounded-lg mb-4">
                            <div class="flex justify-between items-center mb-3">
                                <h4 class="font-medium text-gray-800">1. Selecciona los Atributos</h4>
                                <button type="button" wire:click="abrirModalAtributos"
                                    class="text-sm px-3 py-1 bg-indigo-100 text-indigo-700 rounded-md hover:bg-indigo-200 transition-colors">
                                    ➕ Crear Atributo
                                </button>
                            </div>
                            @if ($modoEdit && !empty($variantes_generadas))
                                <div class="mb-3 p-3 bg-yellow-50 rounded border-l-4 border-yellow-400">
                                    <p class="text-sm text-yellow-800">
                                        ⚠️ Cambiar atributos regenerará todas las variantes y perderás los precios/stock
                                        configurados.
                                    </p>
                                    <button type="button" wire:click="regenerarVariantes"
                                        class="mt-2 text-sm px-3 py-1 bg-yellow-600 text-white rounded hover:bg-yellow-700">
                                        Regenerar Variantes
                                    </button>
                                </div>
                            @endif

                            @if (count($atributos_disponibles) > 0)
                                <div class="grid grid-cols-2 gap-3">
                                    @foreach ($atributos_disponibles as $atributo)
                                        <label
                                            class="flex items-center p-3 border rounded-lg hover:bg-gray-50 cursor-pointer">
                                            <input type="checkbox" wire:model.live="atributos_seleccionados"
                                                value="{{ $atributo->id_atributo }}"
                                                class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                                            <div class="ml-3">
                                                <span class="font-medium text-gray-700">{{ $atributo->nombre }}</span>
                                                @if ($atributo->obligatorio)
                                                    <span class="text-red-500 text-sm">*</span>
                                                @endif
                                                <div class="text-xs text-gray-500">{{ $atributo->valores->count() }}
                                                    opciones disponibles</div>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-8 text-gray-500">
                                    <p>No hay atributos disponibles.</p>
                                    <p class="text-sm">Crea atributos primero (Color, Talla, etc.)</p>
                                </div>
                            @endif
                        </div>

                        <!-- Variantes Generadas -->
                        @if (!empty($variantes_generadas))
                            <div class="bg-white p-4 rounded-lg">
                                <h4 class="font-medium text-gray-800 mb-3">
                                    2. Configurar Variantes Generadas ({{ count($variantes_generadas) }})
                                </h4>

                                <div class="max-h-96 overflow-y-auto">
                                    <div class="space-y-3">
                                        @foreach ($variantes_generadas as $index => $variante)
                                            <div
                                                class="border rounded-lg p-4 bg-gray-50 hover:bg-gray-100 transition-colors">
                                                <!-- Header con checkbox y descripción -->
                                                <div class="flex items-start gap-3 mb-3">
                                                    <input type="checkbox"
                                                        wire:model="variantes_generadas.{{ $index }}.activa"
                                                        class="mt-1 w-5 h-5 rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                                                    <div class="flex-1">
                                                        <p class="text-sm font-medium text-gray-900">
                                                            {{ $variante['descripcion'] }}
                                                        </p>
                                                    </div>
                                                </div>

                                                <!-- Grid con campos editables -->
                                                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 ml-8">
                                                    <!-- SKU Personalizado -->
                                                    <div>
                                                        <label
                                                            class="block text-xs font-medium text-gray-700 mb-1">SKU</label>
                                                        <input type="text"
                                                            wire:model="variantes_generadas.{{ $index }}.sku_custom"
                                                            placeholder="{{ $this->previsualizarSku($variante) }}"
                                                            class="w-full text-xs font-mono rounded border-gray-300 focus:border-purple-500 focus:ring-purple-500">
                                                        <p class="text-xs text-gray-500 mt-1">
                                                            Auto: {{ $this->previsualizarSku($variante) }}
                                                        </p>
                                                    </div>

                                                    <!-- Precio -->
                                                    <div>
                                                        <label
                                                            class="block text-xs font-medium text-gray-700 mb-1">Precio</label>
                                                        <div class="relative">
                                                            <span
                                                                class="absolute left-2 top-1/2 transform -translate-y-1/2 text-gray-400 text-xs">$</span>
                                                            <input type="number"
                                                                wire:model="variantes_generadas.{{ $index }}.precio"
                                                                step="0.01" placeholder="0.00"
                                                                class="w-full pl-6 text-xs rounded border-gray-300 focus:border-purple-500 focus:ring-purple-500">
                                                        </div>
                                                    </div>

                                                    <!-- Stock -->
                                                    <div>
                                                        <label
                                                            class="block text-xs font-medium text-gray-700 mb-1">Stock</label>
                                                        <input type="number"
                                                            wire:model="variantes_generadas.{{ $index }}.stock"
                                                            min="0" placeholder="0"
                                                            class="w-full text-xs rounded border-gray-300 focus:border-purple-500 focus:ring-purple-500">
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="mt-4 p-3 bg-blue-50 rounded text-sm text-blue-700">
                                    <strong>Funcionamiento inteligente:</strong> Al agregar nuevos atributos, se
                                    mantienen los precios y stocks ya configurados. Solo se agregan las nuevas
                                    combinaciones.
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

                <!-- SECCIÓN 4: IMAGEN Y CONFIGURACIONES -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Imagen -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Imagen del Producto</h3>

                        <div>
                            <label for="imagen" class="block text-sm font-medium text-gray-700 mb-2">Imagen
                                Principal</label>
                            <input type="file" id="imagen" wire:model="imagen" accept="image/*"
                                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100">

                            @error('imagen')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror

                            <div wire:loading wire:target="imagen" class="mt-2">
                                <div class="flex items-center text-sm text-gray-600">
                                    <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-purple-500"
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                            stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                    Cargando imagen...
                                </div>
                            </div>

                            @if ($imagen_actual)
                                <div class="mt-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Imagen Actual:</label>
                                    <img src="{{ asset('storage/' . $imagen_actual) }}"
                                        class="w-32 h-32 object-cover rounded-lg border shadow-sm">
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Configuraciones -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Configuraciones</h3>

                        <div class="space-y-4">
                            <!-- Estado -->
                            <div class="flex items-center justify-between">
                                <div>
                                    <label class="text-sm font-medium text-gray-700">Estado del Producto</label>
                                    <p class="text-xs text-gray-500">Desactiva si no tiene stock disponible</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" wire:model="estado" value="activo" class="sr-only peer">
                                    <div
                                        class="w-11 h-6 bg-gray-200 rounded-full peer peer-focus:ring-4 peer-focus:ring-purple-300 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600">
                                    </div>
                                    <span class="ml-3 text-sm text-gray-700">
                                        {{ $estado === 'activo' ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </label>
                            </div>

                            <!-- Destacado -->
                            <div class="flex items-center justify-between">
                                <div>
                                    <label class="text-sm font-medium text-gray-700">Producto Destacado</label>
                                    <p class="text-xs text-gray-500">Aparece en secciones especiales</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" wire:model="destacado" class="sr-only peer">
                                    <div
                                        class="w-11 h-6 bg-gray-200 rounded-full peer peer-focus:ring-4 peer-focus:ring-purple-300 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600">
                                    </div>
                                    <span class="ml-3 text-sm text-gray-700">
                                        {{ $destacado ? 'Sí' : 'No' }}
                                    </span>
                                </label>
                            </div>

                            <!-- Catálogo -->
                            <div class="flex items-center justify-between">
                                <div>
                                    <label class="text-sm font-medium text-gray-700">Mostrar en Catálogo</label>
                                    <p class="text-xs text-gray-500">Visible para clientes</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" wire:model="mostrar_feed" class="sr-only peer">
                                    <div
                                        class="w-11 h-6 bg-gray-200 rounded-full peer peer-focus:ring-4 peer-focus:ring-purple-300 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600">
                                    </div>
                                    <span class="ml-3 text-sm text-gray-700">
                                        {{ $mostrar_feed ? 'Sí' : 'No' }}
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer con botones -->
            <div
                class="bg-gray-50 border-t border-gray-200 p-6 flex flex-col sm:flex-row sm:justify-end space-y-3 sm:space-y-0 sm:space-x-3">
                <button wire:click="closeModal" type="button"
                    class="w-full sm:w-auto inline-flex justify-center rounded-md border border-gray-300 px-6 py-3 bg-white text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-purple-500">
                    Cancelar
                </button>
                <button wire:click.prevent="guardar" type="button"
                    class="w-full sm:w-auto inline-flex justify-center rounded-md border border-transparent px-6 py-3 bg-purple-600 text-sm font-medium text-white shadow-sm hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 disabled:opacity-50 disabled:cursor-not-allowed"
                    wire:loading.attr="disabled" wire:target="guardar, imagen">

                    <div wire:loading.remove wire:target="guardar">
                        {{ $modoEdit ? 'Actualizar Artículo' : 'Crear Artículo' }}
                    </div>

                    <div wire:loading wire:target="guardar" class="flex items-center">
                        <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        {{ $modoEdit ? 'Actualizando...' : 'Creando...' }}
                    </div>
                </button>
            </div>
        </form>
    </div>
    <!-- Modal para gestión de atributos -->
    @if ($mostrar_modal_atributos)
        <div class="fixed inset-0 z-[60] overflow-hidden">
            <div class="absolute inset-0 bg-black bg-opacity-75"></div>
            <div class="absolute inset-0 flex items-center justify-center p-4">
                <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-hidden">
                    <!-- Header -->
                    <div class="bg-gradient-to-r from-indigo-500 to-purple-600 text-white p-6">
                        <div class="flex justify-between items-center">
                            <div>
                                <h3 class="text-lg font-semibold">Crear Nuevo Atributo</h3>
                                <p class="text-indigo-100 text-sm">Define un atributo como Color, Talla, Material, etc.
                                </p>
                            </div>
                            <button wire:click="cerrarModalAtributos" class="text-white hover:text-gray-200">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Contenido -->
                    <div class="p-6 max-h-96 overflow-y-auto">
                        <div class="space-y-4">
                            <!-- Nombre del atributo -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nombre del Atributo
                                    *</label>
                                <input type="text" wire:model="atributo_nombre"
                                    class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="Ej: Color, Talla, Material">
                            </div>

                            <!-- Tipo de atributo -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Atributo *</label>
                                <select wire:model="atributo_tipo"
                                    class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="select">Lista de Opciones</option>
                                    <option value="color">Color</option>
                                    <option value="talla">Talla</option>
                                    <option value="texto">Texto Libre</option>
                                    <option value="numero">Número</option>
                                </select>
                            </div>

                            <!-- Obligatorio -->
                            <div class="flex items-center">
                                <input type="checkbox" wire:model="atributo_obligatorio"
                                    class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <label class="ml-2 text-sm text-gray-700">Este atributo es obligatorio</label>
                            </div>

                            <!-- Valores del atributo -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Valores Disponibles
                                    *</label>

                                <!-- Agregar nuevo valor -->
                                <div class="flex gap-2 mb-3">
                                    <input type="text" wire:model="nuevo_valor"
                                        class="flex-1 rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                        placeholder="Agregar valor..." wire:keydown.enter="agregarValor">
                                    <button type="button" wire:click="agregarValor"
                                        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                                        Agregar
                                    </button>
                                </div>

                                <!-- Lista de valores -->
                                @if (!empty($atributo_valores))
                                    <div class="space-y-2 max-h-32 overflow-y-auto border rounded-md p-2">
                                        @foreach ($atributo_valores as $index => $valor)
                                            <div class="flex items-center gap-2 p-2 bg-gray-50 rounded">
                                                @if ($atributo_tipo === 'color')
                                                    <input type="color"
                                                        wire:model="atributo_valores.{{ $index }}.color_hex"
                                                        class="w-8 h-8 border border-gray-300 rounded">
                                                @endif
                                                <span class="flex-1">{{ $valor['valor'] }}</span>
                                                <button type="button"
                                                    wire:click="eliminarValor({{ $index }})"
                                                    class="text-red-500 hover:text-red-700">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                                        stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-gray-500 text-sm italic">No hay valores agregados</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                        <button wire:click="cerrarModalAtributos" type="button"
                            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                            Cancelar
                        </button>
                        <button wire:click="guardarAtributo" type="button"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                            Crear Atributo
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
