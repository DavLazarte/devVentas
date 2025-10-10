<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tienda Dux</title>
    
    <!-- PWA Meta Tags -->
    <meta name="description" content="Marketplace de tiendas, productos y servicios">
    <meta name="theme-color" content="#8b5cf6">
    <meta name="msapplication-TileColor" content="#8b5cf6">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Tienda DuX">
    <meta name="mobile-web-app-capable" content="yes">
    
    <!-- PWA Icons -->
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('icons/icon-192x192.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('icons/icon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('icons/icon-16x16.png') }}">
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    
    <!-- Tailwind -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    
    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
     
    @livewireStyles
    
    <style>
        .hide-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .hide-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
</head>

<body class="flex flex-col min-h-screen bg-gray-50">
    <main class="flex-grow">
         @if(isset($slot))
            {{ $slot }}
        @endif
     </main>

     @stack('modals')
    @livewireScripts
    @stack('js')
    
    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}"></script>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        window.addEventListener('showAlert', event => {
            Swal.fire({
                title: event.detail.title,
                text: event.detail.message,
                icon: event.detail.type,
                confirmButtonText: 'Entendido',
                confirmButtonColor: '#6B46C1'
            });
        });
    </script>
    
   <!-- PWA Installation Script -->
<script>
    // Registrar Service Worker con actualización automática
    if ('serviceWorker' in navigator) {
      window.addEventListener('load', function() {
        navigator.serviceWorker.register('/sw.js', { scope: '/' })
          .then(function(registration) {
            console.log('SW registrado: ', registration);
            
            // Buscar actualizaciones cada vez que carga la página
            registration.update();
            
            // Buscar actualizaciones cada 5 minutos
            setInterval(() => {
              registration.update();
            }, 5 * 60 * 1000);
            
            // Escuchar si hay una nueva versión disponible
            registration.addEventListener('updatefound', function() {
              const newWorker = registration.installing;
              
              newWorker.addEventListener('statechange', function() {
                if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                  // Hay una nueva versión disponible
                  console.log('Nueva versión disponible. Recargando...');
                  window.location.reload();
                }
              });
            });
          })
          .catch(function(registrationError) {
            console.log('SW falló: ', registrationError);
          });
      });
    }
  </script>
</body>
</html>