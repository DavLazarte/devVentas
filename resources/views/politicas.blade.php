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
                Política de Privacidad
            </h1>
            
            <div class="space-y-6 text-gray-700">
                <section>
                    <h2 class="text-xl font-semibold text-gray-900 mb-3">1. Datos que recolectamos</h2>
                    <p class="ml-4">Nombre, email, teléfono, dirección y otros datos necesarios para gestionar un pedido o una cuenta.</p>
                </section>

                <section>
                    <h2 class="text-xl font-semibold text-gray-900 mb-3">2. Finalidad</h2>
                    <p class="ml-4">Utilizamos los datos para brindar el servicio, gestionar pedidos y mejorar la experiencia de usuario.</p>
                </section>

                <section>
                    <h2 class="text-xl font-semibold text-gray-900 mb-3">3. Compartir información</h2>
                    <p class="ml-4">No compartimos datos personales con terceros, salvo requerimiento legal o necesidad operativa para cumplir con el servicio.</p>
                </section>

                <section>
                    <h2 class="text-xl font-semibold text-gray-900 mb-3">4. Seguridad</h2>
                    <p class="ml-4">Aplicamos medidas razonables para proteger los datos almacenados en nuestros servidores.</p>
                </section>

                <section>
                    <h2 class="text-xl font-semibold text-gray-900 mb-3">5. Derechos</h2>
                    <p class="ml-4">Los usuarios pueden solicitar el acceso, corrección o eliminación de sus datos escribiendo a 
                        <a href="mailto:contacto@tiendadux.com" class="text-purple-600 hover:text-purple-800">contacto@tiendadux.com</a>.
                    </p>
                </section>

                <section>
                    <h2 class="text-xl font-semibold text-gray-900 mb-3">6. Cookies</h2>
                    <p class="ml-4">Podemos usar cookies para mejorar la navegación, pero no para recopilar datos sensibles.</p>
                </section>

                <section>
                    <h2 class="text-xl font-semibold text-gray-900 mb-3">7. Modificaciones</h2>
                    <p class="ml-4">Nos reservamos el derecho a actualizar esta política. Cualquier cambio será informado en la plataforma.</p>
                </section>
            </div>

            <div class="mt-8 pt-6 border-t border-gray-200 text-center text-sm text-gray-500">
                Última actualización: {{ date('d/m/Y') }}
            </div>
        </div>
    </div>
</x-app-layout>
