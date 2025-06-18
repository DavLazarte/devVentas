<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tienda Dux</title>
    <!-- tailwind -->
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
</body>
</html>
