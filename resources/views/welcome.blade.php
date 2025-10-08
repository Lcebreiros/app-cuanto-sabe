<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cuanto Sabe - Bienvenido</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600&family=Montserrat:wght@300;400&display=swap" rel="stylesheet">
    <style>
        /* --- RESET Y VARIABLES --- */
        :root {
            --primary-color: #00f0ff;
            --secondary-color: #ff00ff;
            --dark-bg: #030015;
            --card-bg: rgba(15, 6, 43, 0.85);
            --text-light: #e6f7ff;
            --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        /* --- BASE STYLES --- */
        html, body {
            min-height: 100vh;
            width: 100vw;
            overflow-x: hidden;
            font-family: 'Montserrat', sans-serif;
            background: radial-gradient(circle at center, #210054 60%, var(--dark-bg) 100%);
            color: var(--text-light);
            position: relative;
        }
        
        /* --- FONDO DE PARTÍCULAS --- */
        .particles-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            z-index: 1;
            pointer-events: none;
            overflow: hidden;
        }
        
        .particle {
            position: absolute;
            border-radius: 50%;
            filter: blur(12px);
            opacity: 0.4;
            animation: float 8s infinite ease-in-out alternate;
        }
        
        .particle:nth-child(1) {
            width: 160px; height: 160px;
            background: var(--primary-color);
            top: 10%; left: 13%;
            animation-duration: 9s;
        }
        
        .particle:nth-child(2) {
            width: 110px; height: 110px;
            background: var(--secondary-color);
            top: 70%; left: 80%;
            animation-delay: 2s;
            animation-duration: 11s;
        }
        
        .particle:nth-child(3) {
            width: 80px; height: 80px;
            background: #00ffd1;
            top: 66%; left: 27%;
            animation-delay: 4s;
        }
        
        @keyframes float {
            to { 
                transform: translateY(-60px) scale(1.1) rotate(16deg); 
                opacity: 0.6; 
            }
        }
        
        /* --- CONTENEDOR PRINCIPAL --- */
        .main-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            position: relative;
            z-index: 2;
            padding: 20px;
        }
        
        /* --- TARJETA DE PRESENTACIÓN --- */
        .welcome-card {
            width: 100%;
            max-width: 450px;
            padding: 40px 30px;
            background: var(--card-bg);
            border-radius: 24px;
            box-shadow: 0 10px 30px rgba(0, 240, 255, 0.2),
                        0 0 60px rgba(133, 4, 236, 0.1);
            border: 1px solid rgba(0, 240, 255, 0.2);
            backdrop-filter: blur(8px);
            text-align: center;
            position: relative;
            overflow: hidden;
            transition: var(--transition);
        }
        
        .welcome-card::before {
            content: "";
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, 
                      rgba(0, 240, 255, 0.1) 0%, 
                      transparent 70%);
            z-index: -1;
            animation: rotate 20s linear infinite;
        }
        
        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        /* --- LOGO --- */
        .logo {
            width: 180px;
            height: auto;
            margin: 0 auto 20px;
            filter: drop-shadow(0 0 15px var(--primary-color));
            transition: var(--transition);
        }
        
        /* --- BOTÓN --- */
        .enter-btn {
            display: inline-block;
            width: 100%;
            max-width: 300px;
            padding: 14px 0;
            margin: 20px auto;
            background: transparent;
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
            font-family: 'Orbitron', sans-serif;
            font-size: 1.2rem;
            font-weight: 500;
            border-radius: 30px;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            position: relative;
            overflow: hidden;
            z-index: 1;
        }
        
        .enter-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, 
                      transparent, 
                      rgba(0, 240, 255, 0.2), 
                      transparent);
            transition: all 0.6s ease;
            z-index: -1;
        }
        
        .enter-btn:hover {
            background: var(--primary-color);
            color: #00122c;
            box-shadow: 0 0 20px var(--primary-color),
                       0 0 40px rgba(0, 240, 255, 0.3);
            transform: translateY(-2px);
        }
        
        .enter-btn:hover::before {
            left: 100%;
        }
        
        /* --- TEXTO --- */
        .section-title {
            font-family: 'Orbitron', sans-serif;
            font-size: 1.1rem;
            color: var(--primary-color);
            margin: 25px 0 10px;
            letter-spacing: 1px;
        }
        
        .description {
            font-size: 0.95rem;
            line-height: 1.6;
            margin-bottom: 15px;
            opacity: 0.9;
        }
        
        /* --- AVATARES DEL EQUIPO --- */
        .team-members {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 20px;
        }
        
        .member-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(145deg, #10022d 40%, var(--primary-color) 120%);
            border: 1.5px solid rgba(255, 0, 255, 0.5);
            box-shadow: 0 0 15px rgba(0, 240, 255, 0.5);
            position: relative;
            overflow: hidden;
        }
        
        .member-avatar::after {
            content: '';
            position: absolute;
            width: 40%;
            height: 20%;
            left: 30%;
            top: 15%;
            background: linear-gradient(90deg, rgba(255, 255, 255, 0.4), transparent 80%);
            opacity: 0.4;
            border-radius: 50%;
        }
        
        /* --- EFECTOS DE TRANSICIÓN --- */
        .fade-out {
            animation: fadeOut 0.8s cubic-bezier(0.42, 0.13, 0.54, 1.04) forwards;
        }
        
        @keyframes fadeOut {
            0% { opacity: 1; transform: scale(1); }
            100% { opacity: 0; transform: scale(1.05); }
        }
        
        .ripple-effect {
            position: absolute;
            border-radius: 50%;
            background: radial-gradient(circle, 
                      rgba(200, 220, 255, 0.3) 0%, 
                      transparent 70%);
            transform: scale(0);
            opacity: 0.8;
            pointer-events: none;
            animation: ripple 0.8s ease-out forwards;
        }
        
        @keyframes ripple {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }
        
        /* --- RESPONSIVE --- */
        @media (max-width: 600px) {
            .welcome-card {
                padding: 30px 20px;
            }
            
            .logo {
                width: 150px;
            }
            
            .enter-btn {
                padding: 12px 0;
                font-size: 1.1rem;
            }
        }
        
        @media (max-width: 400px) {
            .welcome-card {
                padding: 25px 15px;
            }
            
            .member-avatar {
                width: 40px;
                height: 40px;
            }
        }
    </style>
</head>
<body>
    <!-- Fondo de partículas -->
    <div class="particles-container">
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
    </div>

    <!-- Contenido principal -->
    <div class="main-container">
        <div class="welcome-card" id="welcomeCard">
            <img src="{{ asset('images/logo.png') }}" alt="Logo Cuanto Sabe" class="logo">
            
            <a href="{{ route('guest-dashboard') }}" class="enter-btn" id="enterBtn">
                Entrar
            </a>
            
            <h3 class="section-title">¿Qué es?</h3>
            <p class="description">
                Un juego donde solo los que más saben llegan al podio.<br>
                ¡Probá tus conocimientos en vivo!
            </p>
            
            <h3 class="section-title">El equipo</h3>
            <div class="team-members">
                <div class="member-avatar"></div>
                <div class="member-avatar"></div>
                <div class="member-avatar"></div>
            </div>
        </div>
    </div>

    <script>
        const welcomeCard = document.getElementById('welcomeCard');
        const enterBtn = document.getElementById('enterBtn');
        
        enterBtn.addEventListener('click', (e) => {
            e.preventDefault();
            
            // Crear efecto ripple
            const ripple = document.createElement('div');
            ripple.classList.add('ripple-effect');
            
            const rect = enterBtn.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            ripple.style.left = `${x}px`;
            ripple.style.top = `${y}px`;
            enterBtn.appendChild(ripple);
            
            // Animación de salida
            welcomeCard.classList.add('fade-out');
            
            // Redirección después de la animación
            setTimeout(() => {
                window.location.href = enterBtn.href;
            }, 700);
        });
    </script>
</body>
</html>