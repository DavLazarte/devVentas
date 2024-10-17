    <div id="loading-overlay" class="fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-75 z-50 ">
        <div class="text-center">
            <div class="loader ease-linear rounded-full border-8 border-t-8 border-gray-200 h-16 w-16 mx-auto mb-4">
            </div>
            <p class="text-white text-lg font-semibold">Ejecutando...</p>
        </div>
    </div>

    <style>
        /* Loader Animation */
        .loader {
            border-top-color: #3498db;
            /* Color del borde superior (azul) */
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        /* Centrado y estilo para el overlay */
        #loading-overlay {
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: rgba(0, 0, 0, 0.75);
            /* Fondo semi-transparente */
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 9999;
            /* Asegura que esté por encima de otros elementos */
        }
    </style>
