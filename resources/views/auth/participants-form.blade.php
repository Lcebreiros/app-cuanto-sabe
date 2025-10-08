<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Registro - Cuanto Sabe</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&display=swap" rel="stylesheet">
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/laravel-echo/1.15.0/echo.iife.js"></script>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html, body {
            height: 100%;
            font-family: 'Orbitron', Arial, sans-serif;
            background: radial-gradient(circle at center, #1e0047 70%, #1e0047 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        .neon-form-container {
            border-radius: 1.5rem;
            box-shadow: 0 0 25px #00f0ff44, 0 0 25px #70007022;
            padding: 2rem;
            width: 100%;
            max-width: 400px;
            border: 1.5px solid #00f0ff55;
            animation: fadeIn 0.7s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            background: transparent;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .logo {
            max-width: 160px;
            margin-bottom: 1.5rem;
        }
        .neon-title {
            font-size: 1.5rem;
            color: #00f0ff;
            text-shadow: 0 0 13px #00f0ffbb, 0 0 6px #fff2;
            text-align: center;
            margin-bottom: 0.5rem;
        }
        .neon-sub {
            font-size: 1rem;
            color: #b2e2ff;
            text-align: center;
            margin-bottom: 1.5rem;
        }
        .neon-input {
            width: 100%;
            padding: 0.8rem 1rem;
            margin-bottom: 1rem;
            border-radius: 0.75rem;
            border: 1.5px solid #00f0ff77;
            background-color: #171c33;
            color: #fff;
            font-size: 1rem;
            transition: 0.2s;
        }
        .neon-input:focus {
            border-color: #00f0ff;
            box-shadow: 0 0 8px #00f0ff77;
            background-color: #1b213a;
        }
        .neon-btn {
            width: 100%;
            padding: 0.9rem;
            border-radius: 2rem;
            border: 2px solid #00f0ff;
            background-color: transparent;
            color: #00f0ff;
            font-weight: bold;
            font-size: 1rem;
            cursor: pointer;
            text-shadow: 0 0 6px #00f0ff55;
            box-shadow: 0 0 10px #00f0ff22;
            transition: 0.2s;
        }
        .neon-btn:hover {
            background-color: #001f2f;
            color: #fff;
            border-color: #fff;
            box-shadow: 0 0 20px #00f0ffcc;
        }
        .success-message, .error-message {
            width: 100%;
            text-align: center;
            font-weight: bold;
            padding: 1rem;
            border-radius: 0.75rem;
            margin-bottom: 1.5rem;
        }
        .success-message {
            background: rgba(25, 255, 140, 0.1);
            color: #19ff8c;
            border: 1px solid #19ff8c;
        }
        .error-message {
            background: rgba(255, 68, 68, 0.1);
            color: #ff4444;
            border: 1px solid #ff4444;
        }
        @media (max-width: 400px) {
            .neon-title { font-size: 1.25rem; }
            .neon-input, .neon-btn { font-size: 0.95rem; }
        }
    </style>
</head>
<body>
    <div class="neon-form-container">
        <img src="/images/logo.png" alt="Logo Cuanto Sabe" class="logo" />

        @if(session('success'))
            <div class="success-message">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="error-message">{{ session('error') }}</div>
        @endif

        <div class="neon-title">¡Registrate para jugar!</div>
        <div class="neon-sub">Completá tus datos para entrar a la cola y jugar en vivo.</div>

        <form id="registration-form" action="{{ route('participants.add') }}" method="POST" autocomplete="off">
            @csrf
            <input type="text" name="participants[0][username]" class="neon-input" placeholder="Nombre o Apodo" required>
            <input type="text" name="participants[0][dni_last4]" class="neon-input" placeholder="Últimos 4 del DNI" maxlength="4" required pattern="\d{4}">
            <button type="submit" class="neon-btn">Registrarme</button>
        </form>
    </div>

    <script>
        window.Pusher = Pusher;
        window.Echo = new Echo({
            broadcaster: 'pusher',
            key: '{{ env('PUSHER_APP_KEY') }}',
            cluster: '{{ env('PUSHER_APP_CLUSTER') }}',
            forceTLS: true
        });

        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('registration-form');
            const button = form.querySelector('button');
            form.addEventListener('submit', () => {
                button.textContent = 'Registrando...';
                button.disabled = true;
            });

            @php
                $activeSession = \App\Models\GameSession::where('status', 'active')->latest()->first();
            @endphp

            @if($activeSession)
                const sessionId = {{ $activeSession->id }};
                const channel = window.Echo.channel(`queue-session-${sessionId}`);

                channel.listen('.ParticipantQueueUpdated', (e) => {
                    console.log("🎉 Evento recibido", e);
                    const success = document.querySelector('.success-message');
                    if (!success) {
                        const div = document.createElement('div');
                        div.className = 'success-message';
                        div.textContent = '¡Te registraste correctamente! Ya estás en la cola.';
                        form.parentNode.insertBefore(div, form);
                    }
                    //setTimeout(() => window.location.href = '/admin', 3000);
                });
            @endif
        });
    </script>
</body>
</html>
