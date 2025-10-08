<div class="control-panel">
    <div class="panel-container">
        <img src="/images/logo.png" alt="Logo Cuanto Sabe" class="panel-logo" />
        <h2 class="panel-title">Centro de Control</h2>

        <div class="panel-grid">
            <a href="/questions" class="panel-btn">
                <span class="btn-text">Preguntas</span>
                <span class="btn-hover-effect"></span>
            </a>
            
            <a href="{{ route('juego.panel') }}" class="panel-btn">
                <span class="btn-text">Juego</span>
                <span class="btn-hover-effect"></span>
            </a>

            <a href="/chat" class="panel-btn">
                <span class="btn-text">Chat</span>
                <span class="btn-hover-effect"></span>
            </a>

                        <a href="{{ route('admin.team') }}" class="panel-btn">
                <span class="btn-text">Equipo</span>
                <span class="btn-hover-effect"></span>
            </a>

                        <a href="{{ route('admin.rules') }}" class="panel-btn">
                <span class="btn-text">Reglas</span>
                <span class="btn-hover-effect"></span>
            </a>
            
            @auth
                @if (auth()->user()->role === 'admin')
                    <a href="{{ route('users') }}" class="panel-btn admin-btn">
                        <span class="btn-text">Admin</span>
                        <span class="btn-hover-effect"></span>
                    </a>
                @endif
            @endauth
        </div>
    </div>
</div>

<style>
    /* --- VARIABLES --- */
    :root {
        --primary-accent: #00f0ff;
        --secondary-accent: #ff00ff;
        --panel-bg: rgba(5, 5, 20, 0.92);
        --btn-bg: rgba(0, 0, 0, 0.4);
        --text-primary: #ffffff;
        --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
    }

    /* --- PANEL CONTAINER --- */
    .control-panel {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        padding: 2rem;
        background: radial-gradient(circle at center, #0a0a2a 0%, #030315 100%);
    }

    .panel-container {
        background: var(--panel-bg);
        border-radius: 18px;
        padding: 3rem 4rem;
        box-shadow: 0 0 30px rgba(0, 240, 255, 0.25),
                    0 0 60px rgba(0, 240, 255, 0.15),
                    inset 0 0 10px rgba(0, 240, 255, 0.1);
        border: 1px solid rgba(0, 240, 255, 0.2);
        backdrop-filter: blur(8px);
        display: flex;
        flex-direction: column;
        align-items: center;
        max-width: 650px;
        width: 100%;
        animation: fadeIn 0.8s ease-out forwards;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* --- LOGO --- */
    .panel-logo {
        width: 220px;
        height: auto;
        margin-bottom: 0.5rem;
        filter: drop-shadow(0 0 12px var(--secondary-accent));
        transition: transform 0.4s ease;
    }

    .panel-logo:hover {
        transform: scale(1.05) rotate(-2deg);
    }

    /* --- TITLE --- */
    .panel-title {
        font-size: 1.8rem;
        color: var(--primary-accent);
        text-shadow: 0 0 8px var(--primary-accent);
        margin: 1rem 0 2.5rem;
        font-weight: 600;
        letter-spacing: 1px;
        position: relative;
    }

    .panel-title::after {
        content: '';
        position: absolute;
        bottom: -10px;
        left: 50%;
        transform: translateX(-50%);
        width: 60px;
        height: 2px;
        background: var(--primary-accent);
        box-shadow: 0 0 8px var(--primary-accent);
    }

    /* --- BUTTON GRID --- */
    .panel-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1.5rem;
        width: 100%;
    }

    @media (max-width: 600px) {
        .panel-grid {
            grid-template-columns: 1fr;
        }
    }

    /* --- BUTTONS --- */
    .panel-btn {
        position: relative;
        background: var(--btn-bg);
        border: 1.5px solid var(--primary-accent);
        color: var(--text-primary);
        padding: 1.5rem 2rem;
        text-decoration: none;
        border-radius: 8px;
        overflow: hidden;
        transition: var(--transition);
        display: flex;
        justify-content: center;
        align-items: center;
        box-shadow: 0 0 15px rgba(0, 240, 255, 0.3);
        z-index: 1;
    }

    .btn-text {
        position: relative;
        font-weight: 500;
        letter-spacing: 0.5px;
        z-index: 2;
    }

    .btn-hover-effect {
        position: absolute;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, 
                  rgba(0, 240, 255, 0.1) 0%, 
                  rgba(0, 240, 255, 0.3) 100%);
        transform: translateY(100%);
        transition: transform 0.4s cubic-bezier(0.65, 0, 0.35, 1);
        z-index: 1;
    }

    .panel-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 0 25px rgba(0, 240, 255, 0.5),
                    0 5px 15px rgba(0, 240, 255, 0.2);
    }

    .panel-btn:hover .btn-hover-effect {
        transform: translateY(0);
    }

    /* ADMIN BUTTON SPECIAL STYLE */
    .admin-btn {
        border-color: var(--secondary-accent);
        box-shadow: 0 0 15px rgba(255, 0, 255, 0.3);
    }

    .admin-btn .btn-hover-effect {
        background: linear-gradient(135deg, 
                  rgba(255, 0, 255, 0.1) 0%, 
                  rgba(255, 0, 255, 0.3) 100%);
    }

    .admin-btn:hover {
        box-shadow: 0 0 25px rgba(255, 0, 255, 0.5),
                    0 5px 15px rgba(255, 0, 255, 0.2);
    }

    /* --- RESPONSIVE ADJUSTMENTS --- */
    @media (max-width: 768px) {
        .panel-container {
            padding: 2.5rem 2rem;
        }
        
        .panel-logo {
            width: 180px;
        }
        
        .panel-title {
            font-size: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .panel-btn {
            padding: 1.2rem 1.5rem;
        }
    }

    @media (max-width: 480px) {
        .control-panel {
            padding: 1rem;
        }
        
        .panel-container {
            padding: 2rem 1.5rem;
            border-radius: 14px;
        }
        
        .panel-logo {
            width: 150px;
        }
        
        .panel-title {
            font-size: 1.3rem;
        }
    }
</style>