<?php

namespace App\Http\Livewire\Feed;

use Livewire\Component;
use App\Models\Articulo;
use App\Models\Servicio;
use Illuminate\Support\Facades\Storage;
use TCG\Voyager\Facades\Voyager;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;



class ProductDetail extends Component
{
    // Constantes para optimización
    private const SLOT_INTERVAL_MINUTES = 30;
    private const TODAY_BUFFER_MINUTES = 30;
    private const TODAY_FALLBACK_MINUTES = 15;
    private const DESCRIPTION_TRUNCATE_LENGTH = 200;

    public $product;
    public $type;
    public $isFavorite = false;
    public $currentImageIndex = 0;
    public $showFullDescription = false;
    public $quantity = 1;

    // Nuevas propiedades para servicios
    public $selectedDate = null;
    public $selectedTime = null;
    public $availableSlots = [];
    public $currentMonth;
    public $currentYear;
    public $showDatePicker = false;

    public $selectedEmployeeId = null;

    protected $listeners = ['$refresh'];

    public function mount($type = null, $id = null)
    {
        $this->type = $type;

        if ($type && $id) {
            if ($type === 'articulo') {
                $this->product = Articulo::with(['categoria', 'local'])->findOrFail($id);
            } else {
                $this->product = Servicio::with(['categoria', 'local', 'empleados'])->findOrFail($id);
            }
    
            // Esta lógica ahora funcionará correctamente
            if ($this->type === 'servicio' && $this->product->empleados->count() === 1) {
                $this->selectedEmployeeId = $this->product->empleados->first()->idpersona;
            }
    
            if ($this->type === 'servicio') {
                $this->currentMonth = now()->month;
                $this->currentYear = now()->year;
            }
        }
    }


    public function toggleFavorite()
    {
        $this->isFavorite = !$this->isFavorite;
    }

    public function setCurrentImage($index)
    {
        $this->currentImageIndex = $index;
    }

    public function toggleDescription()
    {
        $this->showFullDescription = !$this->showFullDescription;
    }

    public function contactSeller()
    {
        // TODO: Implementar lógica de contacto
    }

    public function updateQuantity($newQuantity)
    {
        if ($newQuantity > 0) {
            $this->quantity = $newQuantity;
        }
    }

    // Nuevos métodos para servicios
    public function toggleDatePicker()
    {
        $this->showDatePicker = !$this->showDatePicker;
    }

    public function selectDate($date)
    {
        if ($this->selectedEmployeeId) {
            $this->selectedDate = $date;
            $this->loadAvailableSlots($date);
        } else {
            session()->flash('error', 'Por favor, primero selecciona un empleado para continuar.');
        }
    }

    public function selectTime($time)
    {
        $this->selectedTime = $time;
    }

    public function loadAvailableSlots($date)
    {
        // Limpiar slots antes de generar nuevos
        $this->availableSlots = [];

        // Obtener el día de la semana (1 = lunes, 7 = domingo)
        $dayOfWeek = Carbon::parse($date)->dayOfWeekIso;

        // Obtener los empleados asociados a este servicio
        $empleados = $this->product->empleados;

        if ($empleados->isEmpty()) {
            return; // No hay empleados, no se muestran horarios.
        }

        // Cache key para evitar recálculos innecesarios
        // $cacheKey = "slots_{$this->product->idservicio}_{$date}_{$dayOfWeek}";

        // Obtener todas las reservas de servicios y bloqueos para la fecha seleccionada
        $allReservas = \App\Models\DetallePedido::whereHas('pedido', function ($query) use ($date) {
            $query->where('fecha_servicio', $date)
                ->where('estado_reserva', '!=', 'cancelada');
        })
            // ->where('idservicio', $this->product->idservicio)
            ->get();

        $allBloqueos = \App\Models\BloqueoHorario::where('id_local', $this->product->id_local)
            ->where('fecha', $date)
            ->get();

        $today = Carbon::today();
        $selectedDate = Carbon::parse($date);
        $now = now();

        $empleadoSlots = [];

        // Por cada empleado, generar sus slots disponibles
        foreach ($empleados as $empleado) {
            $slots = [];

            // Obtener los horarios específicos del local para el empleado
            $horariosBase = \App\Models\HorarioDisponibilidad::where('id_local', $this->product->id_local)
                ->where('idservicio', $this->product->idservicio)
                ->where('dia_semana', $dayOfWeek)
                ->where('activo', true)
                ->get();

            // Generar todos los turnos posibles para el empleado
            foreach ($horariosBase as $horario) {
                $inicio = Carbon::parse($horario->hora_inicio);
                $fin = Carbon::parse($horario->hora_fin);

                // Ajustar el inicio si la fecha es hoy
                if ($selectedDate->isSameDay($today)) {
                    // Permitir slots que empiecen en los próximos minutos definidos
                    $minStartTime = $now->copy()->addMinutes(self::TODAY_BUFFER_MINUTES);
                    $inicio = max($inicio, $minStartTime);

                    // Si no hay slots disponibles con el buffer principal, intentar con el fallback
                    if ($inicio->gte($fin)) {
                        $minStartTime = $now->copy()->addMinutes(self::TODAY_FALLBACK_MINUTES);
                        $inicio = max($inicio, $minStartTime);
                    }
                }

                while ($inicio->lt($fin)) {
                    $slotFin = $inicio->copy()->addMinutes($this->product->duracion);
                    $slotFinConBuffer = $slotFin->copy()->addMinutes($this->product->buffer_tiempo ?? 0);

                    if ($slotFinConBuffer->lte($fin)) {
                        $slots[] = $inicio->format('H:i');
                    }
                    // $inicio->addMinutes(self::SLOT_INTERVAL_MINUTES);
                     $inicio->addMinutes($this->product->duracion + ($this->product->buffer_tiempo ?? 0));

                }
            }

            // Filtrar los turnos no disponibles para este empleado
            $availableSlots = [];
            foreach ($slots as $slot) {
                $slotStart = Carbon::parse($date . ' ' . $slot);
                $slotEnd = $slotStart->copy()->addMinutes($this->product->duracion);

                $isAvailable = true;

                // Verificar si el turno choca con alguna reserva de ESTE empleado
                foreach ($allReservas as $reserva) {
                    if ($reserva->id_empleado == $empleado->idpersona) {
                        $reservaStart = Carbon::parse($reserva->pedido->fecha_servicio . ' ' . $reserva->pedido->hora_inicio);
                        $reservaEnd = $reservaStart->copy()->addMinutes($reserva->duracion ?? $this->product->duracion)->addMinutes($this->product->buffer_tiempo ?? 0);

                        if ($slotStart->lt($reservaEnd) && $slotEnd->gt($reservaStart)) {
                            $isAvailable = false;
                            break;
                        }
                    }
                }
                if (!$isAvailable) continue;

                // Verificar si el turno choca con algún bloqueo
                foreach ($allBloqueos as $bloqueo) {
                    if ($bloqueo->id_empleado === null || $bloqueo->id_empleado == $empleado->idpersona) {
                        $bloqueoStart = Carbon::parse($bloqueo->fecha . ' ' . $bloqueo->hora_inicio);
                        $bloqueoEnd = Carbon::parse($bloqueo->fecha . ' ' . $bloqueo->hora_fin);

                        if ($slotStart->lt($bloqueoEnd) && $slotEnd->gt($bloqueoStart)) {
                            $isAvailable = false;
                            break;
                        }
                    }
                }

                if ($isAvailable) {
                    $availableSlots[] = $slot;
                }
            }
            $empleadoSlots[$empleado->idpersona] = $availableSlots;
        }

        $this->availableSlots = $empleadoSlots;
    }

    public function selectEmployee($id)
    {
        $this->selectedEmployeeId = $id;
        // Reinicia la fecha y horarios para que se carguen los del empleado
        $this->selectedDate = null;
        $this->selectedTime = null;
        $this->availableSlots = [];
    }

    public function previousMonth()
    {
        if ($this->currentMonth == 1) {
            $this->currentMonth = 12;
            $this->currentYear--;
        } else {
            $this->currentMonth--;
        }
    }

    public function nextMonth()
    {
        if ($this->currentMonth == 12) {
            $this->currentMonth = 1;
            $this->currentYear++;
        } else {
            $this->currentMonth++;
        }
    }

    public function addToCart($id, $quantity)
    {
        // Validar servicio con turno
        if (!$this->validateServiceReservation()) {
            return;
        }

        // Verificar disponibilidad final
        if (!$this->validateSlotAvailability()) {
            return;
        }

        $cart = session()->get('cart', []);
        $itemData = $this->buildItemData($id, $quantity);
        $itemKey = $this->buildItemKey($id);

        // Verificar si el carrito está vacío
        if (empty($cart)) {
            $cart[$itemKey] = $itemData;
            session()->put('cart', $cart);
            $this->emit('cartItemAdded', count($cart));
            return;
        }

        // Verificar local del carrito
        if (!$this->validateCartShop($cart)) {
            return;
        }

        // Agregar al carrito
        if (isset($cart[$itemKey])) {
            $cart[$itemKey]['quantity'] += $quantity;
        } else {
            $cart[$itemKey] = $itemData;
        }

        session()->put('cart', $cart);
        $this->emit('cartItemAdded', count($cart));

        $this->resetServiceSelection();
    }

    public function reserveServiceDirectly($id, $quantity)
    {
        // Validar servicio con turno
        if (!$this->validateServiceReservation()) {
            return;
        }

        // Verificar disponibilidad final
        if (!$this->validateSlotAvailability()) {
            return;
        }

        $serviceItem = $this->buildItemData($id, $quantity);
        session()->put('direct_service_booking', $serviceItem);
        $this->resetServiceSelection();

        return redirect()->route('checkout', ['direct_service' => true]);
    }

    // Métodos auxiliares extraídos para optimización
    private function validateServiceReservation()
    {
        if ($this->type === 'servicio' && $this->product->tipo_reserva === 'turno_fijo') {
            if (!$this->selectedDate || !$this->selectedTime) {
                $this->dispatchBrowserEvent('showAlert', [
                    'type' => 'warning',
                    'title' => '¡Atención!',
                    'message' => 'Por favor selecciona fecha y hora para tu reserva.'
                ]);
                return false;
            }
        }
        return true;
    }

    private function validateSlotAvailability()
    {
        if ($this->type === 'servicio' && $this->product->tipo_reserva === 'turno_fijo') {
            $slotInicio = Carbon::parse($this->selectedDate . ' ' . $this->selectedTime);
            $slotFin = $slotInicio->copy()->addMinutes($this->product->duracion);

            // Verificar si el slot ya está ocupado justo antes de reservar
            $reservaExistente = DB::table('pedidos')
                ->join('detalle_pedidos', 'pedidos.id', '=', 'detalle_pedidos.pedido_id')
                ->join('servicios', 'detalle_pedidos.idservicio', '=', 'servicios.idservicio')
                ->where('pedidos.fecha_servicio', $this->selectedDate)
                ->where('pedidos.id_local', $this->product->id_local)
                ->where('pedidos.estado_reserva', '!=', 'cancelada')
                ->where(function ($query) use ($slotInicio, $slotFin) {
                    $query->where(function ($q) use ($slotInicio, $slotFin) {
                        $q->whereRaw("CONCAT(fecha_servicio, ' ', hora_inicio) < ?", [$slotFin])
                            ->whereRaw("ADDTIME(CONCAT(fecha_servicio, ' ', hora_inicio), SEC_TO_TIME(servicios.duracion * 60)) > ?", [$slotInicio]);
                    });
                })->exists();

            // Verificar superposición con bloqueos
            $bloqueoExistente = DB::table('bloqueos_horario')
                ->where('fecha', $this->selectedDate)
                ->where('id_local', $this->product->id_local)
                ->where(function ($query) use ($slotInicio, $slotFin) {
                    $query->whereRaw("CONCAT(fecha, ' ', hora_inicio) < ?", [$slotFin])
                        ->whereRaw("CONCAT(fecha, ' ', hora_fin) > ?", [$slotInicio]);
                })->exists();

            if ($reservaExistente || $bloqueoExistente) {
                session()->flash('error', 'El horario que seleccionaste ya no está disponible. Por favor, elige otro.');
                $this->loadAvailableSlots($this->selectedDate);
                return false;
            }
        }
        return true;
    }

    private function buildItemData($id, $quantity)
    {
        $itemData = [
            'id' => $id,
            'type' => $this->type,
            'name' => $this->product->nombre,
            'price' => $this->product->precio_unitario,
            'quantity' => $quantity,
            'image' => $this->product->imagen_url,
            'shop' => $this->product->local->nombre,
            'shop_id' => $this->product->local->id
        ];

        // Agregar datos de reserva para servicios
        if ($this->type === 'servicio' && $this->product->tipo_reserva === 'turno_fijo') {
            $itemData['fecha_servicio'] = $this->selectedDate;
            $itemData['hora_inicio'] = $this->selectedTime;
            $itemData['duracion'] = $this->product->duracion;
            $itemData['id_empleado'] = $this->selectedEmployeeId;
            $empleado = $this->product->empleados->firstWhere('idpersona', $this->selectedEmployeeId);
            if ($empleado) {
                $itemData['empleado_nombre'] = $empleado->nombre;
            }
        }

        return $itemData;
    }

    private function buildItemKey($id)
    {
        if ($this->type === 'servicio' && $this->product->tipo_reserva === 'turno_fijo') {
            return 'service_' . $id . '_' . $this->selectedDate . '_' . $this->selectedTime;
        }
        return $this->type === 'articulo' ? 'product_' . $id : 'service_' . $id;
    }

    private function validateCartShop($cart)
    {
        $firstItem = reset($cart);
        $currentShopId = $firstItem['shop_id'];

        if ($this->product->local->id !== $currentShopId) {
            $this->dispatchBrowserEvent('showAlert', [
                'type' => 'warning',
                'title' => '¡Atención!',
                'message' => 'Por favor, finaliza el pedido actual antes de agregar productos de otro local.'
            ]);
            return false;
        }
        return true;
    }

    private function resetServiceSelection()
    {
        if ($this->type === 'servicio') {
            $this->selectedDate = null;
            $this->selectedTime = null;
            $this->availableSlots = [];
            $this->showDatePicker = false;
        }
    }

    public function render()
    {
        return view('livewire.feed.product-detail')->layout('layouts.app');
    }
}

