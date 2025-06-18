<x-app-layout>
    <div class="max-w-4xl mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-lg p-8">
            <!-- Botón Volver -->
            <div class="mb-6">
                <a href="javascript:history.back()" class="inline-flex items-center text-purple-600 hover:text-purple-800">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                    </svg>
                    Volver
                </a>
            </div>

            <h1 class="text-3xl font-bold text-gray-900 mb-8 text-center">
                Términos y Condiciones
            </h1>
            
            <div class="space-y-6 text-gray-700">
                <p class="text-lg text-center mb-8">
                    Bienvenido/a a Tienda Dux. Al utilizar nuestros servicios, aceptás los siguientes términos:
                </p>

                <section>
                    <h2 class="text-xl font-semibold text-gray-900 mb-3">1. Uso del Servicio</h2>
                    <p class="ml-4">Tienda Dux permite a los locales gestionar productos y recibir pedidos a través de una plataforma digital.</p>
                </section>

                <section>
                    <h2 class="text-xl font-semibold text-gray-900 mb-3">2. Responsabilidad del Local</h2>
                    <p class="ml-4">Cada comercio es responsable por la información publicada, disponibilidad de productos/servicios y cumplimiento con el cliente.</p>
                </section>

                <section>
                    <h2 class="text-xl font-semibold text-gray-900 mb-3">3. Pedidos</h2>
                    <p class="ml-4">Los pedidos realizados no generan obligación de pago en línea. La confirmación y cierre de la venta se realiza directamente entre el cliente y el local.</p>
                </section>

                <section>
                    <h2 class="text-xl font-semibold text-gray-900 mb-3">4. Modificaciones</h2>
                    <p class="ml-4">Tienda Dux se reserva el derecho de modificar estos términos en cualquier momento. Las modificaciones serán informadas en el sitio.</p>
                </section>

                <section>
                    <h2 class="text-xl font-semibold text-gray-900 mb-3">5. Contacto</h2>
                    <p class="ml-4">Ante cualquier duda, escribinos a 
                        <a href="mailto:contacto@tiendadux.com" class="text-purple-600 hover:text-purple-800">contacto@tiendadux.com</a>.
                    </p>
                </section>
            </div>

            <div class="mt-8 pt-6 border-t border-gray-200 text-center text-sm text-gray-500">
                Última actualización: {{ date('d/m/Y') }}
            </div>
        </div>
    </div>
</x-app-layout>
