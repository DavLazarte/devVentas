  <!-- Recommended Sections -->
  @php

      $recommendations = [
          ['title' => 'Todos los Salones', 'image' => asset('images/salones.avif'), 'actionLabel' => 'MUY PRONTO DISPONIBLES'],
          ['title' => 'Tiendas Cercanas', 'image' => asset('images/tiendas.avif'), 'actionLabel' => 'MUY PRONTO DISPONIBLES'],
          ['title' => 'Servicios Premium', 'image' => asset('images/servicios.jpg'), 'actionLabel' => 'MUY PRONTO DISPONIBLES'],
      ];
  @endphp
  <section class="py-4 px-4">
      <h2 class="text-lg font-semibold mb-3">Recomendados</h2>
      <div class="space-y-4">
          @foreach ($recommendations as $recommendation)
              <div class="relative h-32 rounded-lg overflow-hidden">
                  <img src="{{ $recommendation['image'] }}" alt="{{ $recommendation['title'] }}"
                      class="object-cover w-full h-full">
                  <div class="absolute inset-0 bg-black bg-opacity-40 flex items-center justify-center">
                      <div class="text-center">
                          <h3 class="text-white text-xl font-bold mb-2">{{ $recommendation['title'] }}</h3>
                          <button
                              class="bg-purple-600 text-white px-6 py-1 rounded-md text-sm">{{ $recommendation['actionLabel'] }}</button>
                      </div>
                  </div>
              </div>
          @endforeach
      </div>
  </section>
