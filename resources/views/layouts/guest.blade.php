<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon" />
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon" />
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <title>{{ config('app.name', 'Cuanto Sabe') }}</title>

    <!-- Fuentes -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500&display=swap" rel="stylesheet" />

    <!-- Scripts y CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Orbitron', sans-serif;
            background: radial-gradient(circle at center, #1e0047, #0c0125);
            color: #00f0ff;
            margin: 0;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 1rem;
        }
    </style>
</head>
<body>
    <div class="w-full max-w-md sm:max-w-lg bg-[rgba(5,5,20,0.85)] rounded-xl p-8 shadow-[0_0_20px_rgba(0,240,255,0.4)] text-white flex flex-col items-center">
        <a href="/" class="mb-8 block">
            <img src="{{ asset('images/logo.png') }}" alt="Logo Cuanto Sabe" class="w-40 drop-shadow-[0_0_10px_rgba(0,240,255,0.8)]" />
        </a>
        {{ $slot }}
    </div>
</body>
</html>
