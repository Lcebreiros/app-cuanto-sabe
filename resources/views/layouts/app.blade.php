<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon" />
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon" />
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <title>{{ config('app.name', 'Cuanto Sabe') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500&display=swap" rel="stylesheet" />

    <!-- Scripts/CSS Dinámicos -->
    @php
        $manifestPath = public_path('build/manifest.json');
        $manifest = file_exists($manifestPath) ? json_decode(file_get_contents($manifestPath), true) : [];
        $cssFile = $manifest['resources/css/app.css']['file'] ?? null;
        $jsFile  = $manifest['resources/js/app.js']['file'] ?? null;
    @endphp

    @if($cssFile)
        <link rel="stylesheet" href="{{ asset('build/' . $cssFile) }}">
    @endif

    <!-- Livewire Styles -->
    @livewireStyles

    <!-- Estilos custom -->
    <style>
        body { font-family: 'Orbitron', sans-serif; background: radial-gradient(circle at center, #1e0047, #0c0125); color: #00f0ff; margin:0; }
        .antialiased { -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; }
        nav.nav-neon-bar, nav.nav-neon-bar * { border: none !important; border-bottom: none !important; }
        .nav-elegant-shadow { box-shadow: 0 4px 20px rgba(0,240,255,.15),0 2px 8px rgba(0,240,255,.25),0 0 40px rgba(0,240,255,.08); border-bottom:1px solid rgba(0,240,255,.2); }
        header.bg-white { background-color: rgba(5,5,20,.9) !important; box-shadow:0 0 15px rgba(0,240,255,.2); color:#00f0ff; }
        main { padding:1.5rem 0; }
        a { color:#00f0ff; } a:hover { color:#1be5ff; }
    </style>

    <!-- Pusher / Echo: NO TOCAR -->
    <script src="https://js.pusher.com/7.2/pusher.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/laravel-echo/1.11.3/echo.iife.js"></script>
    <script>
        window.Pusher = Pusher;
        window.Echo = new Echo({
            broadcaster: 'pusher',
            key: '{{ env('PUSHER_APP_KEY') }}',
            cluster: '{{ env('PUSHER_APP_CLUSTER') }}',
            forceTLS: true,
        });
    </script>
</head>
<body class="antialiased min-h-screen flex flex-col">

    @if (!isset($hideNavigation) || !$hideNavigation)
        @include('layouts.navigation')
    @endif

    @hasSection('header')
        <header>
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                @yield('header')
            </div>
        </header>
    @endif

    <div class="flex-grow w-full max-w-none flex flex-col min-h-screen px-2 sm:px-4">
        <main class="flex-grow">
            @yield('content')
        </main>

        <footer class="mt-8 py-4 text-center text-sm text-[#00f0ff80]">
            © {{ date('Y') }} {{ config('app.name', 'Cuanto Sabe') }}. Todos los derechos reservados.
        </footer>
    </div>

    <!-- Livewire Scripts -->
    @livewireScripts

    <!-- Tu JS compilado -->
    @if($jsFile)
        <script src="{{ asset('build/' . $jsFile) }}" defer></script>
    @endif

    <!-- Scripts personalizados que usan Livewire o Alpine -->
    @stack('scripts')

    <script>
        // Esperar a que Livewire esté cargado para usarlo con Alpine
        document.addEventListener('livewire:load', function () {
            window.livewireGameControl = {
                refresh: () => Livewire.emit('refreshGameControl')
            };
        });
    </script>
</body>
</html>
