<div class="mt-4">
  <div wire:ignore id='calendar'></div>
</div>

@push('js')
  {{-- <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.19/index.global.min.js'></script> --}}
  <script>
      document.addEventListener('livewire:load', function () {
          var calendarEl = document.getElementById('calendar');

          var calendar = new FullCalendar.Calendar(calendarEl, {
              locale: 'es', // Opcional: para que el calendario esté en español
              initialView: 'dayGridMonth',
              headerToolbar: {
                  left: 'prev,next today',
                  center: 'title',
                  right: 'dayGridMonth,timeGridWeek,timeGridDay'
              },
              events: @json($reservas),
              eventClick: function(info) {
                  // Aquí puedes emitir un evento a Livewire para abrir el modal de detalles
                  Livewire.emit('verDetallePedido', info.event.id);
              }
          });

          calendar.render();

          // Esto es crucial para que el calendario se refresque si cambias de vista y vuelves
          Livewire.on('refreshCalendar', (events) => {
              calendar.removeAllEvents();
              calendar.addEventSource(events);
          });
      });
  </script>
@endpush