<div>
    <!-- Header -->
    <header class="sticky top-0 z-10 bg-white shadow-sm">
        <div class="container mx-auto px-4 py-3 flex items-center justify-between">
            <!-- Back Button -->
            <button onclick="history.back()" class="p-2 -ml-2 rounded-full hover:bg-gray-100">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-600" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </button>

            <!-- Title -->
            <h1 class="text-lg font-semibold text-gray-900 truncate mx-4">{{ $product->nombre }}</h1>

            <!-- Actions -->
            <div class="flex items-center space-x-2">
                <!-- Share Button -->
                <button onclick="shareContent()" class="p-2 rounded-full hover:bg-gray-100">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-600" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z" />
                    </svg>
                </button>

                <!-- Favorite Button -->
                {{-- <button wire:click="toggleFavorite" class="p-2 rounded-full hover:bg-gray-100">
                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="h-6 w-6 {{ $isFavorite ? 'text-red-500 fill-current' : 'text-gray-600' }}" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                    </svg>
                </button> --}}
            </div>
        </div>
    </header>

    <main class="pb-24">
        <!-- Product Image -->
        <div class="relative bg-white">
            <div class="aspect-square relative">
                @if ($product->imagen)
                    <img src="{{ Voyager::image($product->imagen) }}" alt="{{ $product->nombre }}"
                        class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full bg-purple-50 flex items-center justify-center">
                        <span class="text-6xl font-bold text-purple-200 uppercase select-none">
                            {{ substr($product->nombre, 0, 1) }}
                        </span>
                    </div>
                @endif

                <!-- Featured Badge -->
                @if ($product->destacado)
                    <div
                        class="absolute top-4 left-4 bg-purple-600 text-white px-3 py-1 rounded-full text-sm font-medium">
                        Destacado
                    </div>
                @endif
            </div>
        </div>

        <!-- Product Info -->
        <div class="bg-white px-4 py-4 mb-2">
            <!-- Price and Name -->
            <div class="flex items-start justify-between mb-3">
                <div class="flex-1">
                    <h1 class="text-xl font-bold text-gray-900 mb-1">{{ $product->nombre }}</h1>
                    <div class="flex items-center space-x-2">
                        <span class="text-2xl font-bold text-purple-600">
                            @if ($type === 'articulo' && $product->tiene_variantes)
                                @php
                                    $variant =
                                        isset($selectedVariantId) && $selectedVariantId
                                            ? $product->variantesActivas->firstWhere('id_variante', $selectedVariantId)
                                            : null;
                                    $displayPrice = $variant
                                        ? $variant->precio_unitario
                                        : $product->precio_minimo ?? $product->precio_unitario;
                                @endphp
                                ${{ number_format($displayPrice, 2) }}
                            @else
                                ${{ number_format($product->precio_unitario, 2) }}
                            @endif
                        </span>
                        @if ($product->precio_original)
                            <span
                                class="text-lg text-gray-500 line-through">${{ number_format($product->precio_original, 2) }}</span>
                            <span class="bg-red-100 text-red-600 px-2 py-0.5 rounded text-sm font-medium">
                                -{{ round((($product->precio_original - $product->precio) / $product->precio_original) * 100) }}%
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Tags/Categories -->
            @if ($product->categoria)
                <div class="flex flex-wrap gap-2 mb-4">
                    <span
                        class="bg-purple-100 text-purple-700 px-3 py-1 rounded-full text-sm">{{ $product->categoria->nombre }}</span>
                </div>
            @endif

            <!-- Rating and Reviews -->
            @if ($product->rating_promedio)
                <div class="flex items-center space-x-2 mb-4">
                    <div class="flex items-center">
                        @for ($i = 1; $i <= 5; $i++)
                            <svg class="h-4 w-4 {{ $i <= $product->rating_promedio ? 'text-yellow-400' : 'text-gray-300' }}"
                                fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                        @endfor
                    </div>
                    <span class="text-sm text-gray-600">{{ number_format($product->rating_promedio, 1) }}</span>
                </div>
            @endif
        </div>

        <!-- Description -->
        <div class="bg-white px-4 py-4 mb-2">
            <h2 class="text-lg font-semibold text-gray-900 mb-3">Descripción</h2>
            <div class="text-gray-700 leading-relaxed">
                <p class="{{ !$showFullDescription && strlen($product->descripcion) > 200 ? 'line-clamp-3' : '' }}">
                    {{ $product->descripcion }}
                </p>
            </div>

            <!-- Show More/Less Button -->
            @if (strlen($product->descripcion) > 200)
                <button wire:click="toggleDescription" class="text-purple-600 font-medium mt-2 text-sm">
                    {{ $showFullDescription ? 'Ver menos' : 'Ver más' }}
                </button>
            @endif
        </div>

        <!-- Seller Info -->
        <div class="bg-white px-4 py-4 mb-2">
            <h2 class="text-lg font-semibold text-gray-900 mb-3">Vendedor</h2>
            <div class="flex items-center space-x-3">
                <!-- Seller Avatar -->
                <div class="w-12 h-12 rounded-full overflow-hidden border border-gray-200">
                    <img src="{{ Storage::url($product->local->foto_logo) }}" alt="{{ $product->local->nombre }}"
                        class="w-full h-full object-cover">
                </div>

                <!-- Seller Info -->
                <div class="flex-1">
                    <h3 class="font-semibold text-gray-900">{{ $product->local->nombre }}</h3>
                    <div class="flex items-center space-x-2">
                        <div class="flex items-center">
                            <svg class="h-4 w-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                            <span
                                class="text-sm text-gray-600 ml-1">{{ number_format($product->local->rating_promedio, 1) }}</span>
                        </div>
                    </div>
                </div>

                <!-- View Shop Button -->
                <a href="{{ route('store.show', $product->local->slug) }}"
                    class="text-purple-600 font-medium text-sm border border-purple-600 px-3 py-1 rounded-md">
                    Ver tienda
                </a>
            </div>
        </div>

        <div class="left-0 right-0 bg-white border-t border-gray-200 p-4 z-10">
            <div class="flex flex-col space-y-3">
                @if ($type === 'articulo')
                    @if ($product->tiene_variantes && $product->variantesActivas && $product->variantesActivas->count() > 0)
                        <!-- Selector de variantes -->
                        <div>
                            <h3 class="text-sm font-medium text-gray-700 mb-2">Selecciona una variante</h3>
                            <div class="space-y-2">
                                @foreach ($product->variantesActivas as $var)
                                    <button wire:click="selectVariant({{ $var->id_variante }})"
                                        class="w-full text-left p-3 rounded-lg border transition-colors
                                            {{ $selectedVariantId == $var->id_variante
                                                ? 'bg-purple-600 text-white border-purple-600'
                                                : 'bg-white text-gray-700 border-gray-300 hover:border-purple-300 hover:bg-purple-50' }}">
                                        <div class="flex items-center justify-between">
                                            <div class="text-sm">
                                                <div class="font-medium">{{ $var->descripcion_variante }}</div>
                                                <div class="text-xs opacity-75">SKU: {{ $var->sku }}</div>
                                            </div>
                                            <div class="text-sm font-semibold">
                                                ${{ number_format($var->precio_unitario, 2) }}
                                            </div>
                                        </div>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    <!-- Cantidad para productos -->
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Cantidad:</span>
                        <div class="flex items-center space-x-2">
                            @php
                                // Detectar si es por peso/volumen
                                $tipoVenta = 'unidad';
                                $unidadMedida = null;

                                if ($product->tiene_variantes && $selectedVariantId) {
                                    $variant = $product->variantesActivas->firstWhere(
                                        'id_variante',
                                        $selectedVariantId,
                                    );
                                    if ($variant) {
                                        $tipoVenta = $variant->tipo_venta ?? 'unidad';
                                        $unidadMedida = $variant->unidad_medida;
                                    }
                                } else {
                                    $tipoVenta = $product->tipo_venta ?? 'unidad';
                                    $unidadMedida = $product->unidad_medida;
                                }

                                $permiteDecimales = $tipoVenta === 'peso' || $tipoVenta === 'volumen';
                            @endphp

                            @if ($permiteDecimales)
                                <!-- Input decimal para peso/volumen -->
                                <div class="flex flex-col items-center">
                                    <input type="number" wire:model="quantity" step="0.001" min="0.001"
                                        class="w-20 text-center text-sm font-medium border border-gray-300 rounded-lg px-2 py-1.5 focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                        placeholder="1.500">
                                    <span class="text-xs text-gray-500 mt-1">{{ $unidadMedida ?? 'kg' }}</span>
                                </div>
                            @else
                                <!-- Botones +/- para unidades -->
                                <button
                                    class="w-8 h-8 bg-gray-100 text-gray-600 rounded-full flex items-center justify-center hover:bg-gray-200 transition-colors active:scale-95"
                                    wire:click="updateQuantity({{ $quantity - 1 }})">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M20 12H4" />
                                    </svg>
                                </button>

                                <span class="text-sm font-medium w-8 text-center">{{ $quantity }}</span>

                                <button
                                    class="w-8 h-8 bg-gray-100 text-gray-600 rounded-full flex items-center justify-center hover:bg-gray-200 transition-colors active:scale-95"
                                    wire:click="updateQuantity({{ $quantity + 1 }})">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 4v16m8-8H4" />
                                    </svg>
                                </button>
                            @endif
                        </div>
                    </div>
                @elseif($type === 'servicio' && $product->tipo_reserva === 'turno_fijo')
                    <!-- Selector de Fecha y Hora para servicios con turno -->

                    <!-- Información del servicio -->
                    <div class="bg-purple-50 p-3 rounded-lg">
                        <div class="flex items-center space-x-2 text-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-purple-600" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="text-purple-700 font-medium">Duración: {{ $product->duracion }}
                                minutos</span>
                        </div>
                    </div>
                    {{-- seleccionar empleado --}}
                    @if ($product->es_reservable && $product->tipo_reserva === 'turno_fijo' && $product->empleados->isNotEmpty())
                        <div class="mt-8">
                            @if ($product->empleados->count() > 1)
                                <h3 class="text-xl font-semibold text-gray-800">1. Selecciona un profesional</h3>
                                <div class="mt-4 grid grid-cols-2 gap-4">
                                    @foreach ($product->empleados as $empleado)
                                        <div wire:click="selectEmployee({{ $empleado->idpersona }})"
                                            class="p-4 rounded-lg text-center cursor-pointer transition-all duration-200
                        {{ $selectedEmployeeId == $empleado->idpersona ? 'bg-purple-600 text-white shadow-lg scale-105' : 'bg-gray-100 hover:bg-gray-200' }}">
                                            <p class="font-medium">{{ $empleado->nombre }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <h3 class="text-xl font-semibold text-gray-800">1. Servicio a cargo de:</h3>
                                <div class="mt-4 grid grid-cols-2 gap-4">
                                    <div
                                        class="p-4 rounded-lg text-center cursor-not-allowed
                    bg-purple-600 text-white shadow-lg scale-105">
                                        <p class="font-medium">{{ $product->empleados->first()->nombre }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- Fecha seleccionada -->
                    @if ($selectedDate)
                        <div
                            class="flex items-center justify-between p-3 {{ \Carbon\Carbon::parse($selectedDate)->isToday() ? 'bg-blue-50 border-blue-200' : 'bg-green-50 border-green-200' }} rounded-lg border">
                            <div class="flex items-center space-x-2">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    class="h-5 w-5 {{ \Carbon\Carbon::parse($selectedDate)->isToday() ? 'text-blue-600' : 'text-green-600' }}"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <div>
                                    <span
                                        class="{{ \Carbon\Carbon::parse($selectedDate)->isToday() ? 'text-blue-800' : 'text-green-800' }} font-medium">
                                        {{ \Carbon\Carbon::parse($selectedDate)->locale('es')->isoFormat('dddd, D [de] MMMM') }}
                                    </span>
                                    @if (\Carbon\Carbon::parse($selectedDate)->isToday())
                                        <div
                                            class="text-xs {{ \Carbon\Carbon::parse($selectedDate)->isToday() ? 'text-blue-600' : 'text-green-600' }} mt-1">
                                            ¡Turnos disponibles para hoy!
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <button wire:click="$set('selectedDate', null)"
                                class="{{ \Carbon\Carbon::parse($selectedDate)->isToday() ? 'text-blue-600 hover:text-blue-800' : 'text-green-600 hover:text-green-800' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    @endif

                    <!-- Hora seleccionada -->
                    @if ($selectedTime)
                        <div
                            class="flex items-center justify-between p-3 bg-blue-50 rounded-lg border border-blue-200">
                            <div class="flex items-center space-x-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="text-blue-800 font-medium">{{ $selectedTime }}</span>
                            </div>
                            <button wire:click="$set('selectedTime', null)" class="text-blue-600 hover:text-blue-800">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    @endif

                    <!-- Botón para seleccionar fecha -->
                    @if ($selectedEmployeeId)
                        @if (!$selectedDate)
                            <button wire:click="toggleDatePicker"
                                class="w-full p-3 border-2 border-dashed border-purple-300 rounded-lg text-purple-600 font-medium hover:border-purple-400 hover:bg-purple-50 transition-colors">
                                <div class="flex items-center justify-center space-x-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <span>Seleccionar fecha</span>
                                </div>
                            </button>
                        @endif
                    @endif

                    <!-- Calendar Picker -->
                    @if ($showDatePicker)
                        <div class="border rounded-lg p-4 bg-gray-50">
                            <!-- Calendar Header -->
                            <div class="flex items-center justify-between mb-4">
                                <button wire:click="previousMonth" class="p-1 rounded hover:bg-gray-200">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 19l-7-7 7-7" />
                                    </svg>
                                </button>

                                <h3 class="font-semibold text-gray-900">
                                    {{ \Carbon\Carbon::create($currentYear, $currentMonth, 1)->locale('es')->isoFormat('MMMM YYYY') }}
                                </h3>

                                <button wire:click="nextMonth" class="p-1 rounded hover:bg-gray-200">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5l7 7-7 7" />
                                    </svg>
                                </button>
                            </div>

                            <!-- Days of week -->
                            <div class="grid grid-cols-7 gap-1 mb-2">
                                @foreach (['L', 'M', 'X', 'J', 'V', 'S', 'D'] as $day)
                                    <div class="text-center text-xs font-medium text-gray-600 py-2">
                                        {{ $day }}</div>
                                @endforeach
                            </div>

                            <!-- Calendar Days -->
                            <div class="grid grid-cols-7 gap-1">
                                @php
                                    $firstDay = \Carbon\Carbon::create($currentYear, $currentMonth, 1);
                                    $lastDay = $firstDay->copy()->endOfMonth();
                                    $startCalendar = $firstDay->copy()->startOfWeek(\Carbon\Carbon::MONDAY);
                                    $endCalendar = $lastDay->copy()->endOfWeek(\Carbon\Carbon::SUNDAY);
                                    $todayDate = now()->startOfDay();
                                @endphp

                                @for ($date = $startCalendar->copy(); $date->lte($endCalendar); $date->addDay())
                                    @php
                                        $isCurrentMonth = $date->month === $currentMonth;
                                        $isToday = $date->isToday();
                                        // Comparar solo por fecha para no marcar "hoy" como pasado
                                        $isPast = $date->lt($todayDate);
                                        $dateString = $date->format('Y-m-d');
                                        $isSelected = isset($selectedDate) && $selectedDate === $dateString;
                                    @endphp

                                    <button wire:click="selectDate('{{ $dateString }}')"
                                        @if ($isPast || !$isCurrentMonth) disabled @endif
                                        class="aspect-square p-1 text-sm rounded-lg transition-colors
                                    {{ $isCurrentMonth ? '' : 'text-gray-300' }}
                                    {{ $isPast ? 'text-gray-300 cursor-not-allowed' : '' }}
                                    {{ $isSelected ? 'bg-purple-600 text-white font-semibold' : ($isToday ? 'bg-purple-50 text-purple-700' : '') }}
                                    {{ !$isPast && $isCurrentMonth && !$isSelected ? 'hover:bg-purple-50 hover:text-purple-600' : '' }}">
                                        {{ $date->day }}
                                    </button>
                                @endfor
                            </div>
                        </div>
                    @endif

                    <!-- Time Slots -->
                    @if (
                        $selectedDate &&
                            $selectedEmployeeId &&
                            isset($availableSlots[$selectedEmployeeId]) &&
                            count($availableSlots[$selectedEmployeeId]) > 0)
                        <div class="space-y-3">
                            <h4 class="font-medium text-gray-900">Horarios disponibles:</h4>
                            <div class="grid grid-cols-3 gap-2">
                                @foreach ($availableSlots[$selectedEmployeeId] as $slot)
                                    <button wire:click="selectTime('{{ $slot }}')"
                                        class="p-2 text-sm rounded-lg border transition-colors
                                    {{ $selectedTime === $slot
                                        ? 'bg-purple-600 text-white border-purple-600'
                                        : 'bg-white text-gray-700 border-gray-300 hover:border-purple-300 hover:bg-purple-50' }}">
                                        {{ $slot }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @elseif(
                        $selectedDate &&
                            (!$selectedEmployeeId ||
                                !isset($availableSlots[$selectedEmployeeId]) ||
                                count($availableSlots[$selectedEmployeeId]) === 0))
                        <div class="p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                            <div class="flex items-center space-x-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-yellow-600"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.966-.833-2.732 0L4.732 15.5c-.77.833.192 2.5 1.732 2.5z" />
                                </svg>
                                <span class="text-yellow-800 text-sm">No hay horarios disponibles para esta
                                    fecha</span>
                            </div>
                        </div>
                    @endif
                @elseif($type === 'servicio' && $product->tipo_reserva === 'coordinacion')
                    <!-- Para servicios de coordinación -->
                    <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                        <div class="flex items-start space-x-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600 mt-0.5"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div>
                                <h4 class="font-medium text-blue-900">Servicio por coordinación</h4>
                                <p class="text-sm text-blue-800 mt-1">
                                    Después de realizar la reserva, nos pondremos en contacto contigo para coordinar
                                    fecha, hora y detalles del servicio.
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Botones de acción -->
                <div class="flex space-x-3">
                    <!-- Contact Seller Button -->
                    <button wire:click="contactSeller"
                        class="flex-1 bg-gray-100 text-gray-700 py-3 px-4 rounded-lg font-medium flex items-center justify-center space-x-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                        <span>Contactar</span>
                    </button>

                    <!-- Add to Cart / Reserve Button -->
                    <button
                        @if ($type === 'articulo') wire:click="addToCart({{ $product->idarticulo }}, {{ $quantity }})"
        {{-- 🔑 CLAVE: Añade estas dos líneas para el loader del artículo --}}
        wire:loading.attr="disabled"
        wire:target="addToCart"
    @else
        wire:click="reserveServiceDirectly({{ $product->idservicio }}, {{ $quantity }})" @endif
                        class="flex-1 bg-purple-600 text-white py-3 px-4 rounded-lg font-medium flex items-center justify-center space-x-2 hover:bg-purple-700 transition-colors
    {{ $type === 'servicio' && $product->tipo_reserva === 'turno_fijo' && (!$selectedDate || !$selectedTime) ? 'opacity-50 cursor-not-allowed' : '' }}"
                        @if ($type === 'servicio' && $product->tipo_reserva === 'turno_fijo' && (!$selectedDate || !$selectedTime)) disabled @endif>


                        {{-- 1. 💡 LOADER/SPINNER (Solo visible si es ARTÍCULO y está cargando) --}}
                        @if ($type === 'articulo')
                            <span wire:loading wire:target="addToCart" class="flex items-center space-x-2">
                                <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                                <span>Agregando...</span>
                            </span>
                        @endif


                        {{-- 2. CONTENIDO NORMAL (Todo tu contenido original, envuelto en un span con wire:loading.remove) --}}
                        {{-- La directiva wire:loading.remove asegura que se oculte SÓLO si addToCart está ejecutándose --}}
                        <span wire:loading.remove wire:target="addToCart">
                            @if ($type === 'articulo')
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                <span>Agregar al carrito</span>
                            @else
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span>
                                    {{ $product->tipo_reserva === 'turno_fijo' ? 'Reservar turno' : 'Solicitar servicio' }}
                                </span>
                            @endif
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </main>

    <livewire:footer-menu />

</div>
<script>
    function shareContent() {
        console.log('Share clicked');
        if (navigator.share) {
            navigator.share({
                title: '{{ $product->nombre }}',
                text: '{{ $product->descripcion }}',
                url: window.location.href
            }).catch(() => copyLink());
        } else {
            copyLink();
        }
    }

    function copyLink() {
        const url = window.location.href;

        // Método 1: Clipboard API (recomendado)
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(url).then(() => {
                console.log('✓ Enlace copiado al portapapeles');
                showNotification('¡Enlace copiado!');
            }).catch(err => {
                console.error('Error al copiar:', err);
                fallbackCopy(url);
            });
        } else {
            // Método 2: Fallback para navegadores antiguos
            fallbackCopy(url);
        }
    }

    function fallbackCopy(text) {
        const textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.style.position = 'fixed';
        textarea.style.opacity = '0';
        document.body.appendChild(textarea);
        textarea.select();

        try {
            document.execCommand('copy');
            console.log('✓ Enlace copiado (método fallback)');
            showNotification('¡Enlace copiado!');
        } catch (err) {
            console.error('Error al copiar:', err);
        }

        document.body.removeChild(textarea);
    }

    function showNotification(message) {
        // Simple toast notification
        const toast = document.createElement('div');
        toast.textContent = message;
        toast.style.cssText = `
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: #10b981;
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 14px;
            z-index: 9999;
            animation: slideUp 0.3s ease;
        `;
        document.body.appendChild(toast);

        setTimeout(() => toast.remove(), 2000);
    }
</script>

<style>
    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateX(-50%) translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
        }
    }
</style>
