@extends('layouts.app')

@section('content')
@php
    $activeSession = \App\Models\GameSession::where('status', 'active')->latest()->first();
@endphp

<style>
    :root {
        --primary-color: #00f0ff;
        --secondary-color: #ff00ff;
        --success-color: #19ff8c;
        --warning-color: #ffcc00;
        --error-color: #ff4444;
        --dark-bg: #0a0e23;
        --card-bg: rgba(15, 18, 42, 0.95);
        --input-bg: rgba(23, 28, 51, 0.8);
        --text-primary: #ffffff;
        --text-secondary: #b8c7ff;
        --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
    }

    .game-panel-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 1rem 2rem;
        display: grid;
        grid-template-columns: 1fr 350px;
        gap: 30px;
    }

    .panel-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
        grid-column: 1 / -1;
        position: relative;
    }

    .header-content {
        display: flex;
        justify-content: center;
        width: 100%;
    }

    .power-toggle-container {
        position: absolute;
        right: 0;
        top: 0;
    }

    .power-btn {
        border: none;
        outline: none;
        border-radius: 50px;
        font-size: 1.2rem;
        font-family: 'Orbitron', sans-serif;
        font-weight: 800;
        padding: 14px 30px 14px 70px;
        letter-spacing: 2px;
        color: #fff;
        position: relative;
        transition: var(--transition);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        cursor: pointer;
        min-width: 200px;
    }

    .power-btn .indicator {
        position: absolute;
        left: 25px;
        top: 50%;
        transform: translateY(-50%);
        width: 28px;
        height: 28px;
        border-radius: 50%;
        border: 2px solid rgba(255, 255, 255, 0.2);
        transition: var(--transition);
    }

    .power-btn.off {
        background: linear-gradient(to right, #2d1313, #4a1a1a);
        color: #ff9a9a;
    }

    .power-btn.off .indicator {
        background: #ff2d3b;
        box-shadow: 0 0 20px #ff2d3b99;
    }

    .power-btn.on {
        background: linear-gradient(to right, #0c3320, #1a4a2d);
        color: #00ffb7;
    }

    .power-btn.on .indicator {
        background: #10ff62;
        box-shadow: 0 0 25px #10ff628f, 0 0 15px #00ffb7bb inset;
    }

    .power-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 25px rgba(0, 0, 0, 0.4);
    }

    .guest-info-container {
        display: flex;
        gap: 20px;
        align-items: center;
        background: linear-gradient(135deg, rgba(0, 240, 255, 0.1) 0%, rgba(255, 0, 255, 0.1) 100%);
        border-radius: 16px;
        padding: 15px 25px;
        border: 1px solid rgba(0, 240, 255, 0.3);
        box-shadow: 0 0 30px rgba(0, 240, 255, 0.2);
        backdrop-filter: blur(5px);
        max-width: 800px;
        margin: 0 auto;
    }

    .guest-avatar {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: linear-gradient(135deg, #00f0ff 0%, #ff00ff 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--dark-bg);
    }

    .guest-details {
        flex: 1;
    }

    .guest-name {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--primary-color);
        margin-bottom: 5px;
        text-shadow: 0 0 10px var(--primary-color);
    }

    .guest-meta {
        display: flex;
        gap: 20px;
    }

    .guest-meta-item {
        font-size: 1rem;
    }

    .guest-meta-item strong {
        color: var(--primary-color);
    }

    .guest-points {
        background: rgba(25, 255, 140, 0.2);
        border-radius: 50px;
        padding: 8px 20px;
        font-weight: 800;
        font-size: 1.3rem;
        display: flex;
        align-items: center;
        border: 1px solid rgba(25, 255, 140, 0.3);
        box-shadow: 0 0 20px rgba(25, 255, 140, 0.1);
    }

    .guest-info-placeholder {
        font-size: 1.2rem;
        color: var(--text-secondary);
        text-align: center;
        width: 100%;
        padding: 20px;
    }

    .main-content {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .spin-btn-container {
        display: flex;
        justify-content: center;
        margin: 20px 0;
        grid-column: 1;
    }

    .spin-btn {
        background: linear-gradient(135deg, #00f0ff 0%, #ff00ff 100%);
        color: #00122c;
        border: none;
        border-radius: 50px;
        padding: 20px 40px;
        font-size: 1.5rem;
        font-weight: 800;
        letter-spacing: 1px;
        text-transform: uppercase;
        cursor: pointer;
        box-shadow: 0 0 40px rgba(0, 240, 255, 0.5),
                    0 0 80px rgba(255, 0, 255, 0.3);
        transition: var(--transition);
        position: relative;
        overflow: hidden;
        min-width: 250px;
    }

    .spin-btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    .spin-btn.spinning {
        background: linear-gradient(135deg, #ff00ff 0%, #ff2d3b 100%);
    }

    .participants-container {
        background: transparent;
        border-radius: 16px;
        overflow: hidden;
        max-height: 600px;
        grid-column: 2;
        grid-row: 2 / span 2;
    }

    .participants-title {
        color: var(--primary-color);
        font-size: 1.2rem;
        margin: 0;
        padding: 15px 20px;
        text-shadow: 0 0 8px var(--primary-color);
        border-bottom: 1px solid rgba(0, 240, 255, 0.3);
        background: rgba(15, 18, 42, 0.5);
    }

    .participants-content {
        padding: 0;
        max-height: 540px;
        overflow-y: auto;
    }

    .empty-queue-message {
        color: var(--text-secondary);
        padding: 20px;
        text-align: center;
    }

    .question-panel {
        background: var(--card-bg);
        border-radius: 16px;
        padding: 25px;
        margin-bottom: 25px;
        border: 1px solid rgba(0, 240, 255, 0.3);
        box-shadow: 0 0 25px rgba(0, 240, 255, 0.1);
        grid-column: 1;
    }

    .question-text {
        font-size: 1.3rem;
        color: #18fff9;
        font-weight: 700;
        text-shadow: 0 0 10px rgba(25, 250, 255, 0.8);
        margin-bottom: 20px;
        min-height: 50px;
        text-align: center;
    }

    .options-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 15px;
        margin-bottom: 20px;
    }

    .option-btn {
        background: #0b1530;
        color: #fff;
        border: 2px solid var(--primary-color);
        border-radius: 12px;
        font-size: 1.1rem;
        font-weight: 700;
        padding: 15px;
        cursor: pointer;
        transition: var(--transition);
        text-align: center;
        min-height: 80px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .option-btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    .option-btn:hover:not(:disabled) {
        background: rgba(25, 250, 255, 0.1);
    }

    .option-btn.selected {
        background: #ffe47a;
        color: #222;
        border-color: #ffe47a;
        box-shadow: 0 0 20px rgba(255, 228, 122, 0.7);
    }

    .panel-actions {
        display: flex;
        justify-content: center;
        gap: 15px;
    }

    .panel-action-btn {
        padding: 12px 25px;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: var(--transition);
        font-size: 1rem;
    }

    .panel-action-btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    .reveal-btn {
        background: var(--success-color);
        color: #00361e;
        border: none;
    }

    .reset-btn {
        background: #111b2b;
        color: var(--primary-color);
        border: 1px solid var(--primary-color);
    }

    .start-game-form {
        background: var(--card-bg);
        border-radius: 16px;
        padding: 25px;
        margin: 0 auto 25px;
        border: 1px solid rgba(0, 240, 255, 0.3);
        box-shadow: 0 0 20px rgba(0, 240, 255, 0.1);
        max-height: 0;
        overflow: hidden;
        opacity: 0;
        transition: max-height 0.4s ease, opacity 0.4s ease;
        max-width: 800px;
        grid-column: 1 / -1;
    }

    .start-game-form.show {
        max-height: 500px;
        opacity: 1;
    }

    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr auto;
        gap: 15px;
        align-items: end;
    }

    .form-group {
        margin-bottom: 0;
    }

    .form-control, .form-select {
        background: var(--input-bg);
        color: var(--text-primary);
        border: 1px solid rgba(37, 45, 67, 0.8);
        border-radius: 8px;
        padding: 12px;
        width: 100%;
    }

    .start-btn {
        background: var(--success-color);
        color: #00361e;
        border: none;
        border-radius: 8px;
        padding: 12px 25px;
        font-weight: 600;
        cursor: pointer;
        transition: var(--transition);
        height: fit-content;
    }

    /* ---- LUZ DE RADIO ---- */
.radio-light-btn {
    border: none;
    outline: none;
    border-radius: 38px;
    font-size: 1.24rem;
    font-family: 'Orbitron', Arial, sans-serif;
    font-weight: bold;
    padding: 12px 30px 12px 60px;
    margin-right: 10px;
    letter-spacing: 2px;
    background: #211;
    color: #fff;
    position: relative;
    transition: background 0.18s, color 0.16s, box-shadow 0.18s, transform 0.15s;
    box-shadow: 0 0 8px #211, 0 0 0 #fff0;
    display: inline-flex;
    align-items: center;
    white-space: nowrap;
    vertical-align: middle;
    /* IMPORTANTE: Mantener tamaño fijo SIEMPRE */
    width: auto !important;
    min-width: 130px !important;
    max-width: 240px !important;
}
.radio-light-btn .light {
    position: absolute;
    left: 16px;
    top: 50%;
    transform: translateY(-50%);
    width: 26px;
    height: 26px;
    border-radius: 50%;
    background: #ff2d3b;
    box-shadow: 0 0 14px #ff2d3b99, 0 0 0 #fff0;
    border: 2px solid #fff2;
    transition: background 0.18s, box-shadow 0.18s, width 0.16s, height 0.16s;
}
.radio-light-btn.off .light { background: #ff2d3b; box-shadow: 0 0 14px #ff2d3b99; }
.radio-light-btn.off { background: #2d1313; color: #ff8888; box-shadow: 0 0 16px #ff2d3b30; }
.radio-light-btn.on .light { background: #10ff62; box-shadow: 0 0 22px #10ff628f, 0 0 14px #00ffb7bb inset; }
.radio-light-btn.on { background: #0c3320; color: #00ffb7; box-shadow: 0 0 28px #19ffa990, 0 0 1px #fff6; }
.radio-light-btn:hover, .radio-light-btn:focus {
    filter: brightness(1.09) contrast(1.07);
    transform: scale(1.04);
    box-shadow: 0 0 24px #00f0ff22, 0 0 0 #fff0;
}
.radio-light-btn:hover .light, .radio-light-btn:focus .light {
    width: 33px;
    height: 33px;
    box-shadow:
        0 0 30px #ffe78caa,
        0 0 50px #fff93333,
        0 0 30px #00ffb7a1,
        0 0 18px #ff2d3b80,
        0 0 38px #15ff90c9,
        0 0 22px #10ff6299;
}

    .random-question-form {
        display: flex;
        gap: 15px;
        align-items: center;
        margin: 0 auto 25px;
        flex-wrap: wrap;
        background: var(--card-bg);
        border-radius: 12px;
        padding: 20px;
        border: 1px solid rgba(0, 240, 255, 0.2);
        max-width: 800px;
        grid-column: 1;
    }

    .form-label {
        color: var(--primary-color);
        font-size: 1rem;
        margin-bottom: 8px;
    }

    .submit-random-btn {
        background: #23ffe5;
        color: #002;
        border: none;
        border-radius: 8px;
        padding: 12px 25px;
        font-weight: 600;
        cursor: pointer;
        transition: var(--transition);
    }

    /* resaltar opción en tendencia */
.option-btn.trend {
    border-color: #22fa68;
    box-shadow: 0 0 18px rgba(34,250,104,.45);
    position: relative;
}
.option-btn.trend::after {
    content: 'TENDENCIA';
    position: absolute;
    top: -10px;
    right: 10px;
    font-size: .8rem;
    font-weight: 800;
    letter-spacing: .5px;
    padding: 2px 8px;
    border-radius: 8px;
    background: linear-gradient(90deg,#22fa68 60%,#19faff 100%);
    color: #0b2314;
}
/* Botón Apuesta x2 - azul profesional */
.apuesta-btn {
    background-color: #2c3e50; /* gris oscuro apagado */
    color: #7f8c8d; /* texto gris apagado */
    border: 2px solid #34495e;
    border-radius: 50px;
    padding: 14px 50px;
    font-size: 1.2rem;
    font-weight: 800;
    text-transform: uppercase;
    cursor: pointer;
    box-shadow: 0 0 8px rgba(44, 62, 80, 0.3);
    transition: all 0.3s ease;
    min-width: 220px;
    opacity: 0.5; /* muy apagado por defecto */
    position: relative;
}

/* Estado activo */
.apuesta-btn.on {
    background: linear-gradient(135deg, #1e90ff 0%, #00bfff 100%);
    color: #fff;
    border-color: #00bfff;
    box-shadow: 
        0 0 25px rgba(0, 191, 255, 0.8), 
        0 0 50px rgba(0, 191, 255, 0.5),
        0 0 5px #fff inset;
    opacity: 1;
    text-shadow: 0 0 10px rgba(255, 255, 255, 0.8);
}

/* Estado inactivo */
.apuesta-btn.off {
    background-color: #1874cd;
    box-shadow: 0 0 12px rgba(24, 116, 205, 0.4), 0 0 25px rgba(24, 116, 205, 0.2);
}

/* Botón Descarte - rojo profesional */
.descarte-btn {
    background-color: #2c3e50; /* gris oscuro apagado */
    color: #7f8c8d; /* texto gris apagado */
    border: 2px solid #34495e;
    border-radius: 50px;
    padding: 14px 50px;
    font-size: 1.2rem;
    font-weight: 800;
    text-transform: uppercase;
    cursor: pointer;
    box-shadow: 0 0 8px rgba(44, 62, 80, 0.3);
    transition: all 0.3s ease;
    min-width: 220px;
    opacity: 0.5; /* muy apagado por defecto */
}

/* Estado activo */
.descarte-btn.on {
    background: linear-gradient(135deg, #ff4500 0%, #ff6347 100%);
    color: #fff;
    border-color: #ff6347;
    box-shadow: 
        0 0 25px rgba(255, 99, 71, 0.8), 
        0 0 50px rgba(255, 99, 71, 0.5),
        0 0 5px #fff inset;
    opacity: 1;
    text-shadow: 0 0 10px rgba(255, 255, 255, 0.8);
}

/* Estado inactivo */
.descarte-btn.off {
    background-color: #cd3700;
    box-shadow: 0 0 12px rgba(205, 55, 0, 0.4), 0 0 25px rgba(205, 55, 0, 0.2);
}

/* Efecto hover suave */
.apuesta-btn:hover, .descarte-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 0 25px rgba(255, 255, 255, 0.5);
}

.power-toggle-container {
    position: absolute;
    right: 0;
    top: 0;
    display: flex;
    flex-direction: row;  /* Horizontal */
    align-items: center;
    gap: 12px;  /* Espacio entre botones */
}

.fullscreen-btn-minimal {
    background: rgba(79, 70, 229, 0.15);
    color: #818cf8;
    border: 1.5px solid rgba(129, 140, 248, 0.3);
    border-radius: 10px;
    padding: 10px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    backdrop-filter: blur(8px);
    width: 44px;
    height: 44px;
    flex-shrink: 0;  /* Evita que se comprima */
}

.fullscreen-btn-minimal:hover {
    background: rgba(79, 70, 229, 0.25);
    border-color: rgba(129, 140, 248, 0.5);
    color: #c7d2fe;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
}

.fullscreen-btn-minimal:active {
    transform: translateY(0);
}

.fullscreen-btn-minimal.active {
    background: rgba(16, 185, 129, 0.2);
    border-color: rgba(52, 211, 153, 0.5);
    color: #6ee7b7;
}

.fullscreen-btn-minimal.active:hover {
    background: rgba(16, 185, 129, 0.3);
    border-color: rgba(52, 211, 153, 0.7);
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.fs-icon {
    transition: transform 0.3s ease;
}

.fullscreen-btn-minimal:hover .fs-icon {
    transform: scale(1.1);
}

/* Responsive: en móvil apilar verticalmente */
@media (max-width: 768px) {
    .power-toggle-container {
        position: static;
        flex-direction: column;
        gap: 10px;
        align-self: flex-end;
        margin-bottom: 15px;
    }
}

    @media (max-width: 1200px) {
        .game-panel-container {
            grid-template-columns: 1fr;
        }
        
        .participants-container {
            grid-column: 1;
            grid-row: auto;
            margin-top: 30px;
        }
    }

    @media (max-width: 992px) {
        .options-grid {
            grid-template-columns: 1fr 1fr;
        }
    }

    @media (max-width: 768px) {
        .panel-header {
            flex-direction: column;
            gap: 15px;
        }
        
        .power-toggle-container {
            position: static;
            align-self: flex-end;
            margin-bottom: 15px;
        }
        
        .guest-info-container {
            flex-direction: column;
            text-align: center;
        }
        
        .guest-meta {
            flex-direction: column;
            gap: 5px;
        }
        
        .form-grid {
            grid-template-columns: 1fr;
        }
        
        .panel-actions {
            flex-direction: column;
        }
        
        .random-question-form {
            flex-direction: column;
            align-items: stretch;
        }
    }

    @media (max-width: 480px) {
        .game-panel-container {
            padding: 1rem;
        }
        
        .spin-btn {
            font-size: 1.2rem;
            padding: 16px 30px;
        }
        
        .power-btn {
            min-width: 180px;
            padding: 12px 20px 12px 60px;
            font-size: 1.1rem;
        }
        
        .guest-name {
            font-size: 1.3rem;
        }
        
        .option-btn {
            min-width: 160px;
        }
        
        .options-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="game-panel-container">
    <!-- Encabezado con información del invitado -->
    <div class="panel-header">
        <div class="header-content">
<div class="guest-info-container">
    @if($activeSession)
        <div class="guest-avatar">
            {{ substr($activeSession->guest_name, 0, 1) }}
        </div>
        <div class="guest-details">
            <div class="guest-name">{{ $activeSession->guest_name }}</div>
            <div class="guest-meta">
                <div class="guest-meta-item">
                    <strong>Motivo:</strong> {{ $activeSession->motivo->nombre ?? '—' }}
                </div>
                <div class="guest-meta-item">
                <!-- PASTILLA DE MODO (sin texto "Modo:") -->
                <span class="mode-pill {{ $activeSession->modo_juego }}">
                    {{ $activeSession->modo_juego === 'express' ? 'Express' : 'Normal' }}
                </span>
                </div>
            </div>
        </div>
<div class="guest-points">
    <span id="guestPointsValue">{{ $activeSession->guest_points ?? 0 }}</span> pts
</div>
    @else
        <div class="guest-info-placeholder">
            No hay sesión activa
        </div>
    @endif
</div>

<style>
.guest-info-container {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 8px 12px;
    background-color: #1f1b2e;
    border-radius: 12px;
    color: #fff;
}

.guest-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background-color: #4f46e5;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 1.1rem;
}

.guest-details {
    flex-grow: 1;
}

.guest-name {
    font-weight: bold;
    font-size: 1rem;
}

.guest-meta {
    display: flex;
    gap: 8px;
    font-size: 0.85rem;
    margin-top: 2px;
}

.guest-meta-item {
    display: flex;
    align-items: center;
    gap: 4px;
}

/* PASTILLA DE MODO MEJORADA */
.mode-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 5px 14px;
    border-radius: 16px;
    font-size: 0.8rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
}

.mode-pill::before {
    content: '';
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: currentColor;
    animation: pulse 2s ease-in-out infinite;
}

.mode-pill.normal {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    color: #fff;
}

.mode-pill.express {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color: #fff;
}
</style>

        </div>
        
        <!-- Botón ON/OFF -->
        <div class="power-toggle-container">
                        <button type="button" 
            id="fullscreenBtn" 
            class="fullscreen-btn-minimal" 
            onclick="toggleFullscreen()"
            title="Pantalla completa (Ctrl+F)">
        <!-- Ícono expandir -->
        <svg class="fs-icon expand" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"/>
        </svg>
        <!-- Ícono contraer -->
        <svg class="fs-icon compress" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="display: none;">
            <path d="M8 3v3a2 2 0 0 1-2 2H3m18 0h-3a2 2 0 0 1-2-2V3m0 18v-3a2 2 0 0 1 2-2h3M3 16h3a2 2 0 0 1 2 2v3"/>
        </svg>
    </button>
           @if(!$activeSession)
            <button type="button"
                class="radio-light-btn off"
                onclick="toggleStartForm('formStartGame')"
                style="display: inline-flex !important; 
                       align-items: center !important; 
                       white-space: nowrap !important;
                       vertical-align: middle !important;">
                <span class="light"></span>
                OFF
            </button>
        @else
            <form action="{{ route('game-session.end') }}" method="POST" 
                  style="display: inline-flex !important; margin: 0 !important; padding: 0 !important;">
                @csrf
                <button type="submit" 
                    class="radio-light-btn on"
                    style="display: inline-flex !important; 
                           align-items: center !important; 
                           white-space: nowrap !important;
                           vertical-align: middle !important;">
                    <span class="light"></span>
                    ON
                </button>
            </form>
        @endif
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <!-- Contenido principal -->
    <div class="main-content">
        <!-- Botón Girar Ruleta -->
        <div class="spin-btn-container">
            <button id="spinButton" class="spin-btn" onclick="toggleSpinButton()" {{ !$activeSession ? 'disabled' : '' }}>
                Girar Ruleta
            </button>
        </div>
        <div class="panel-actions">
    <!-- Botón Apuesta x2 -->
     @php
    // calcular límites y disponibles
    $apuestaLimite = $activeSession ? ($activeSession->isExpress() ? 1 : 2) : 0;
    $apuestaUsadas = $activeSession ? (int)$activeSession->apuesta_x2_usadas : 0;
    $apuestaDisponibles = max(0, $apuestaLimite - $apuestaUsadas);

    $descarteLimite = 1; // según tu regla
    $descarteUsados = $activeSession ? (int)$activeSession->descarte_usados : 0;
    $descarteDisponibles = max(0, $descarteLimite - $descarteUsados);
@endphp
    <form id="form-apuesta-x2">
        @csrf
        <button
            id="apuesta-btn"
            type="submit"
            class="bonus-btn apuesta-btn {{ $activeSession && $activeSession->apuesta_x2_active ? 'on' : 'off' }}"
            data-active="{{ $activeSession && $activeSession->apuesta_x2_active ? '1' : '0' }}"
            data-usadas="{{ $apuestaUsadas }}"
            data-limite="{{ $apuestaLimite }}"
        >
            <span class="light"></span>
            <span class="label">
                {{-- Si quedan 2 mostrar APUESTA X2 (puedes ajustar el texto) --}}
                APUESTA
            </span>
            <span id="apuesta-badge" class="badge" aria-hidden="true">x{{ $apuestaDisponibles }}</span>
        </button>
    </form>

    <!-- Botón Descarte -->
    <form id="form-descarte">
        @csrf
        <button
            id="descarte-btn"
            type="submit"
            class="bonus-btn descarte-btn {{ $descarteDisponibles > 0 ? 'on' : 'off' }}"
            data-usados="{{ $descarteUsados }}"
            data-limite="{{ $descarteLimite }}"
        >
            <span class="light"></span>
            <span class="label">DESCARTE</span>
            <span id="descarte-badge" class="badge" aria-hidden="true">x{{ $descarteDisponibles }}</span>
        </button>
    </form>
</div>


        <!-- Panel de Pregunta -->
        <div class="question-panel">
            <div id="textoPreguntaPanel" class="question-text">
                {{ $activeSession ? 'Pregunta aún no enviada' : 'Inicie una sesión para comenzar' }}
            </div>
            <div class="options-grid">
                <button type="button" class="option-btn" id="panelA" style="display:none;" {{ !$activeSession ? 'disabled' : '' }}></button>
                <button type="button" class="option-btn" id="panelB" style="display:none;" {{ !$activeSession ? 'disabled' : '' }}></button>
                <button type="button" class="option-btn" id="panelC" style="display:none;" {{ !$activeSession ? 'disabled' : '' }}></button>
                <button type="button" class="option-btn" id="panelD" style="display:none;" {{ !$activeSession ? 'disabled' : '' }}></button>
            </div>
            <div class="panel-actions">
<button 
    type="button" 
    id="revealBtn"
    class="panel-action-btn reveal-btn" 
    onclick="revelarRespuesta()" {{ !$activeSession ? 'disabled' : '' }}
>
    Revelar respuesta
</button>

<button type="button" id="botonReiniciar" class="panel-action-btn reset-btn" {{ !$activeSession ? 'disabled' : '' }}>
    Volver a la ruleta
</button>

            </div>
        </div>

        <!-- Formulario Pregunta Random -->
        @if($activeSession)
            <form id="enviarPreguntaRandomForm" class="random-question-form" onsubmit="return enviarPreguntaRandom(event)">
                <div class="form-group">
                    <label for="categoriaRandom" class="form-label">Categoría:</label>
                    <select id="categoriaRandom" class="form-select" required>
                        <option value="">Seleccione categoría</option>
                        @foreach($categorias as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="submit-random-btn">
                    Enviar pregunta random
                </button>
            </form>
        @endif
    </div>

    <!-- Panel de Participantes -->
    <div class="participants-container">
        <h3 class="participants-title">Participantes</h3>
        <div class="participants-content" id="queue-container">
            @if($activeSession)
                @include('components.queue-list', ['participants' => $activeSession->participants])
            @else
                <div class="empty-queue-message">
                    La lista de participantes aparecerá aquí cuando inicie una sesión
                </div>
            @endif
        </div>
    </div>

<!-- Formulario Iniciar Juego -->
<div id="start-game-form" class="start-game-form">
    <form action="{{ route('game-session.start') }}" method="POST">
        @csrf
        <div class="form-grid">
            <!-- Nombre del invitado -->
            <div class="form-group">
                <label>Nombre del invitado:</label>
                <input type="text" name="guest_name" class="form-control" required>
            </div>

            <!-- Motivo -->
            <div class="form-group">
                <label>Motivo:</label>
                <select name="motivo_id" class="form-select" required>
                    <option value="">Seleccione motivo</option>
                    @foreach($motivos as $motivo)
                        <option value="{{ $motivo->id }}">{{ $motivo->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Modo de juego -->
                <!-- Modo de juego MEJORADO -->
                <div class="form-group">
                    <label>Modo de juego:</label>
                    <div class="mode-switch-container">
                        <div class="mode-options">
                            <div class="mode-option">
                                <input type="radio" id="mode_normal" name="modo_juego" value="normal" checked>
                                <label for="mode_normal" class="mode-label">
                                    <span class="mode-name">Normal</span>
                                    <span class="mode-target">Meta: 25 puntos</span>
                                </label>
                            </div>
                            <div class="mode-option">
                                <input type="radio" id="mode_express" name="modo_juego" value="express">
                                <label for="mode_express" class="mode-label">
                                    <span class="mode-name">Express</span>
                                    <span class="mode-target">Meta: 10 puntos</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

            <!-- Botón Iniciar -->
            <button type="submit" class="start-btn">Iniciar Juego</button>
        </div>
    </form>
</div>

<!-- CSS del toggle -->
<style>
/* SWITCH MEJORADO */
        .mode-switch-container {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .mode-options {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            background: rgba(15, 23, 42, 0.4);
            padding: 6px;
            border-radius: 16px;
            border: 2px solid rgba(148, 163, 184, 0.1);
        }

        .mode-option {
            position: relative;
        }

        .mode-option input[type="radio"] {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }

        .mode-label {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 16px 12px;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: transparent;
            border: 2px solid transparent;
            position: relative;
            overflow: hidden;
        }

        .mode-option input:checked + .mode-label {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            border-color: rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 20px rgba(79, 70, 229, 0.4);
            transform: translateY(-2px);
        }

        .mode-option input:not(:checked) + .mode-label:hover {
            background: rgba(79, 70, 229, 0.1);
            border-color: rgba(79, 70, 229, 0.3);
        }

        .mode-name {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 4px;
            color: #e2e8f0;
            transition: color 0.3s;
        }

        .mode-option input:checked + .mode-label .mode-name {
            color: #ffffff;
            text-shadow: 0 0 8px rgba(255, 255, 255, 0.3);
        }

        .mode-target {
            font-size: 0.85rem;
            font-weight: 600;
            color: #94a3b8;
            transition: color 0.3s;
        }

        .mode-option input:checked + .mode-label .mode-target {
            color: #c7d2fe;
        }

        .mode-option input:checked + .mode-label {
            opacity: 1;
        }
</style>


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

// Estado del botón de ruleta
let isSpinning = false;

document.addEventListener('DOMContentLoaded', function () {
    const apuestaForm = document.getElementById('form-apuesta-x2');
    const descarteForm = document.getElementById('form-descarte');

    const apuestaBtn = document.getElementById('apuesta-btn');
    const descarteBtn = document.getElementById('descarte-btn');

    const apuestaBadge = document.getElementById('apuesta-badge');
    const descarteBadge = document.getElementById('descarte-badge');

    // helper UI update
    function updateApuestaUI(payload) {
        // payload: { apuesta_x2_active, apuesta_x2_usadas, apuesta_x2_disponibles, modo_juego }
        const active = !!payload.apuesta_x2_active;
        const usadas = Number(payload.apuesta_x2_usadas || 0);
        const disponibles = Number(payload.apuesta_x2_disponibles ?? 0);

        apuestaBtn.classList.toggle('on', active);
        apuestaBtn.classList.toggle('off', !active);
        apuestaBtn.dataset.active = active ? '1' : '0';
        apuestaBtn.dataset.usadas = usadas;

        apuestaBadge.textContent = 'x' + disponibles;

        // si no quedan disponibles, deshabilitar el botón visualmente
        apuestaBtn.disabled = disponibles <= 0 && !active;
        if (apuestaBtn.disabled) apuestaBtn.style.opacity = 0.6;
        else apuestaBtn.style.opacity = 1;
    }

    function updateDescarteUI(payload) {
        // payload: { descarte_usados, descarte_disponible (opcional) }
        const usados = Number(payload.descarte_usados || 0);
        // si la API devuelve descarte_disponible usalo, sino calcular por limite=1
        const disponibles = typeof payload.descarte_disponible !== 'undefined'
            ? (payload.descarte_disponible ? 1 : 0)
            : Math.max(0, 1 - usados);

        descarteBtn.classList.toggle('on', disponibles > 0);
        descarteBtn.classList.toggle('off', disponibles <= 0);
        descarteBadge.textContent = 'x' + disponibles;
        descarteBtn.disabled = disponibles <= 0;
        if (descarteBtn.disabled) descarteBtn.style.opacity = 0.6;
        else descarteBtn.style.opacity = 1;
    }

    // Fetch: activar/desactivar apuesta
    apuestaForm.addEventListener('submit', function (e) {
        e.preventDefault();
        apuestaBtn.disabled = true; // evitar double click
        fetch("{{ route('game.toggleApuestaX2') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({})
        })
        .then(async res => {
            const data = await res.json();
            if (!res.ok || !data.success) {
                const err = data.error || 'No se pudo activar la apuesta';
                alert(err);
            } else {
                updateApuestaUI({
                    apuesta_x2_active: data.apuesta_x2_active,
                    apuesta_x2_usadas: data.apuesta_x2_usadas,
                    apuesta_x2_disponibles: data.apuesta_x2_disponibles,
                    modo_juego: data.modo_juego ?? null
                });

                // opcional: guardar en session JS si necesitás
                // sessionStorage.setItem('guest_apuesta_x2', data.apuesta_x2_active ? '1' : '0');
            }
        })
        .catch(err => {
            console.error(err);
            alert('Error en la petición (ver consola).');
        })
        .finally(() => {
            apuestaBtn.disabled = false;
        });
    });

    // Fetch: usar descarte
    descarteForm.addEventListener('submit', function (e) {
        e.preventDefault();
        descarteBtn.disabled = true;
        fetch("{{ route('game.toggleDescarte') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({})
        })
        .then(async res => {
            const data = await res.json();
            if (!res.ok || !data.success) {
                const err = data.error || 'No se pudo usar el descarte';
                alert(err);
            } else {
                updateDescarteUI({
                    descarte_usados: data.descarte_usados ?? data.descarteUsados,
                    descarte_disponible: data.descarte_disponible ?? (data.descarte_usados < 1)
                });

                // si el descarte lanza una nueva pregunta aquí puedes manejarlo (por ejemplo refrescar overlay)
            }
        })
        .catch(err => {
            console.error(err);
            alert('Error en la petición (ver consola).');
        })
        .finally(() => {
            descarteBtn.disabled = false;
        });
    });

    // Escuchar broadcasts en overlay-channel -> GameBonusUpdated
    try {
        if (window.Echo) {
            window.Echo.channel('overlay-channel')
                .listen('.GameBonusUpdated', (payload) => {
                    // payload ejemplo: { apuesta_x2_active, apuesta_x2_usadas, descarte_usados, modo_juego }
                    // Acomodar nombres si tu backend envía sin prefijos
                    updateApuestaUI({
                        apuesta_x2_active: payload.apuesta_x2_active ?? payload.apuesta_x2Active,
                        apuesta_x2_usadas: payload.apuesta_x2_usadas ?? payload.apuesta_x2Usadas,
                        apuesta_x2_disponibles: (payload.modo_juego ? ((payload.modo_juego === 'express') ? 1 : 2) : (Number(apuestaBtn.dataset.limite || 2))) - (payload.apuesta_x2_usadas ?? 0),
                        modo_juego: payload.modo_juego
                    });

                    updateDescarteUI({
                        descarte_usados: payload.descarte_usados ?? payload.descarteUsados,
                        // si el backend no envía descarte_disponible, lo calculamos
                    });
                });
        }
    } catch (e) {
        console.warn('Echo/Pusher no disponible: live updates deshabilitadas.', e);
    }

});


// Funciones JavaScript
function toggleStartForm() {
    document.getElementById('start-game-form').classList.toggle('show');
}

function toggleSpinButton() {
    const spinButton = document.getElementById('spinButton');
    isSpinning = !isSpinning;
    
    if (isSpinning) {
        spinButton.textContent = 'Parar Ruleta';
        spinButton.classList.add('spinning');
    } else {
        spinButton.textContent = 'Girar Ruleta';
        spinButton.classList.remove('spinning');
    }
    
    girarRuleta();
}

function girarRuleta() {
    fetch('/game-session/girar-ruleta', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ action: isSpinning ? 'start' : 'stop' })
    });
}

let lastOverlayQuestion = null;


// Función de revelar
function revelarRespuesta() {
    if (!lastOverlayQuestion) {
        console.warn("No hay pregunta activa aún");
        return;
    }

    fetch("{{ route('game-session.reveal') }}", {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": "{{ csrf_token() }}",
            "Content-Type": "application/json"
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            console.log("Respuesta revelada correctamente");
        } else {
            console.warn("No se pudo revelar:", data.error);
        }
    })
    .catch(err => console.error("Error al revelar:", err));
}

// Asignar onclick
const revealBtn = document.getElementById('revealBtn');
if (revealBtn) revealBtn.onclick = revelarRespuesta;


let overlayResetting = false;

function reiniciarOverlay() {
    if (overlayResetting) return;
    overlayResetting = true;

    fetch("{{ route('game-session.overlay-reset') }}", {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": "{{ csrf_token() }}",
            "Content-Type": "application/json"
        }
    }).finally(() => {
        overlayResetting = false;
    });

    document.getElementById('textoPreguntaPanel').textContent = 'Pregunta aún no enviada';
    ['A','B','C','D'].forEach(l => {
        const btn = document.getElementById('panel'+l);
        if (!btn) return;
        btn.style.display = 'none';
        btn.textContent = l;
        btn.classList.remove('selected','trend');
        btn.dataset.baseText = '';
    });

    if (isSpinning) {
        toggleSpinButton();
    }
}

// Asociar el listener después de que el DOM esté cargado
document.addEventListener('DOMContentLoaded', () => {
    const boton = document.getElementById('botonReiniciar');
    if (boton) {
        boton.addEventListener('click', reiniciarOverlay);
    }
});


function enviarPreguntaRandom(e) {
    e.preventDefault();
    const catId = document.getElementById('categoriaRandom').value;
    if (!catId) return false;
    
    fetch("{{ route('game-session.random-question') }}", {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": "{{ csrf_token() }}",
            "Content-Type": "application/json"
        },
        body: JSON.stringify({ categoria_id: catId })
    });
    
    return false;
}

// WebSocket listeners
document.addEventListener('DOMContentLoaded', function() {
    // Selección de opciones
    ['A','B','C','D'].forEach(l => {
        const btn = document.getElementById('panel'+l);
        if (btn) {
            btn.onclick = function() {
                ['A','B','C','D'].forEach(x => {
                    const other = document.getElementById('panel'+x);
                    if (other) other.classList.remove('selected');
                });
                btn.classList.add('selected');
                
                fetch("{{ route('game-session.select-option') }}", {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": "{{ csrf_token() }}",
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({ opcion: l })
                });
            };
        }
    });

    // Actualización de lista de participantes
    @if($activeSession)
        const sessionId = {{ $activeSession->id }};
        const channelName = `queue-session-${sessionId}`;
        
        window.Echo.channel(channelName)
            .listen('.ParticipantQueueUpdated', () => {
                updateQueueList(sessionId);
            });

        function updateQueueList(sessionId) {
            fetch(`/queue-list/${sessionId}`, {
                headers: {
                    'Accept': 'text/html',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.text())
            .then(html => {
                const container = document.getElementById('queue-container');
                if (container) container.innerHTML = html;
            });
        }
    @endif

// --- Overlay channel: nueva pregunta + tendencia + reset ---
if (window.Echo) {
    const overlay = Echo.channel('overlay-channel');
    Echo.channel('overlay-channel')
        .listen('.GameBonusUpdated', (event) => {
            console.log('[PANEL] Evento bonus recibido:', event);

            // Actualizar botón Apuesta x2
            const btnApuesta = document.querySelector('.apuesta-btn');
            if(btnApuesta){
                if(event.apuesta_x2_active){
                    btnApuesta.classList.add('on');
                    btnApuesta.classList.remove('off');
                } else {
                    btnApuesta.classList.remove('on');
                    btnApuesta.classList.add('off');
                }
            }

            // Actualizar botón Descarte
            const btnDescarte = document.querySelector('.descarte-btn');
            if(btnDescarte){
                if(event.descarte_usados > 0){
                    btnDescarte.classList.add('on');
                    btnDescarte.classList.remove('off');
                } else {
                    btnDescarte.classList.remove('on');
                    btnDescarte.classList.add('off');
                }
            }
        });

    // Nueva pregunta
    overlay.listen('.nueva-pregunta', (e) => {
        const data = e.data || e || {};
        const pregunta = data.pregunta || (data.data ? data.data.pregunta : '') || '';
        const opciones = data.opciones || (data.data ? data.data.opciones : []) || [];

        // 🔥 GUARDAR EN VARIABLE GLOBAL
        lastOverlayQuestion = data;
        console.log('✅ Pregunta guardada en lastOverlayQuestion:', lastOverlayQuestion);

        const txt = document.getElementById('textoPreguntaPanel');
        if (txt) txt.textContent = pregunta || 'Pregunta aún no enviada';

        ['A','B','C','D'].forEach((l) => {
            const btn = document.getElementById('panel'+l);
            if (!btn) return;
            const opcion = opciones.find(op => op.label === l);

            if (opcion) {
                btn.style.display = '';
                const base = `${l}: ${opcion.texto}`;
                btn.dataset.baseText = base;
                btn.textContent = base;
            } else {
                btn.style.display = 'none';
                btn.dataset.baseText = '';
                btn.textContent = '';
            }
            btn.classList.remove('selected','trend');
        });

        // Sincronizar pregunta con backend
        fetch("{{ route('game-session.sync-question') }}", {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                "Content-Type": "application/json"
            },
            body: JSON.stringify({ pregunta: data })
        })
        .then(res => res.json())
        .then(result => {
            console.log('✅ Pregunta sincronizada con backend:', result);
        })
        .catch(err => console.error('❌ Error al sincronizar:', err));
    });

    // Tendencia actualizada
    overlay.listen('.tendencia-actualizada', (e) => {
        const payload = e.data || {};
        const label = payload.option_label;
        const total = payload.total;

        // limpiar tendencia previa
        ['A','B','C','D'].forEach(l => {
            const btn = document.getElementById('panel'+l);
            if (!btn) return;
            btn.classList.remove('trend');
            if (btn.dataset.baseText) btn.textContent = btn.dataset.baseText;
        });

        // marcar nueva tendencia
        if (label) {
            const btn = document.getElementById('panel'+label);
            if (btn) {
                btn.classList.add('trend');
                const base = btn.dataset.baseText || btn.textContent || label;
                btn.textContent = `${base} — Tendencia (${total})`;
            }
        }
    });

    // Reset overlay
    overlay.listen('.overlay-reset', () => {
        lastOverlayQuestion = null; // 🔥 LIMPIAR AL RESETEAR
        console.log('🔄 Overlay reseteado, lastOverlayQuestion limpiado');
        reiniciarOverlay();
    });
}

    // Deshabilitar funcionalidades si no hay sesión activa
    if (!@json($activeSession ? true : false)) {
        document.querySelectorAll('.spin-btn, .reveal-btn, .reset-btn, .option-btn').forEach(btn => {
            btn.disabled = true;
        });
    }
});

        // Animación al cambiar de modo
        document.querySelectorAll('input[name="modo_juego"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const label = this.nextElementSibling;
                label.style.transform = 'scale(1.05)';
                setTimeout(() => {
                    label.style.transform = '';
                }, 200);
            });
        });
if (window.Echo) {
    Echo.channel('overlay-channel')
        .listen('.GuestPointsUpdated', e => {
            const val = document.getElementById('guestPointsValue');
            if (val) {
                val.textContent = e.points; // actualiza el valor
            }
        });
}

if (window.Echo) {
    Echo.channel('overlay-channel')
        .listen('.revelar-respuesta', (e) => {
            const payload = e.data || {};

            // Actualizar texto de tendencia en botones
            ['A','B','C','D'].forEach(l => {
                const btn = document.getElementById('panel'+l);
                if (!btn) return;
                btn.classList.remove('trend');
                if (btn.dataset.baseText) btn.textContent = btn.dataset.baseText;
            });

            if (payload.tendencia?.option_label) {
                const trendBtn = document.getElementById('panel'+payload.tendencia.option_label);
                if (trendBtn) {
                    trendBtn.classList.add('trend');
                    const base = trendBtn.dataset.baseText || '';
                    trendBtn.textContent = `${base} — Tendencia (${payload.tendencia.total})`;
                }
            }

            // Aquí podés actualizar puntajes y overlay según payload
            console.log(payload);
        });
}
// Función para activar/desactivar pantalla completa
function toggleFullscreen() {
    const btn = document.getElementById('fullscreenBtn');
    const expandIcon = btn.querySelector('.expand');
    const compressIcon = btn.querySelector('.compress');
    
    if (!document.fullscreenElement) {
        // Entrar en pantalla completa
        document.documentElement.requestFullscreen().then(() => {
            btn.classList.add('active');
            btn.title = 'Salir de pantalla completa (ESC)';
            expandIcon.style.display = 'none';
            compressIcon.style.display = 'block';
        }).catch(err => {
            console.error('Error al activar pantalla completa:', err);
        });
    } else {
        // Salir de pantalla completa
        document.exitFullscreen().then(() => {
            btn.classList.remove('active');
            btn.title = 'Pantalla completa (Ctrl+F)';
            expandIcon.style.display = 'block';
            compressIcon.style.display = 'none';
        }).catch(err => {
            console.error('Error al salir de pantalla completa:', err);
        });
    }
}

// Listener para detectar cambios de pantalla completa (ESC)
document.addEventListener('fullscreenchange', () => {
    const btn = document.getElementById('fullscreenBtn');
    const expandIcon = btn.querySelector('.expand');
    const compressIcon = btn.querySelector('.compress');
    
    if (!document.fullscreenElement) {
        btn.classList.remove('active');
        btn.title = 'Pantalla completa (Ctrl+F)';
        expandIcon.style.display = 'block';
        compressIcon.style.display = 'none';
    }
});

// Atajo de teclado: Ctrl+F
document.addEventListener('keydown', (e) => {
    if (e.key === 'f' && e.ctrlKey) {
        e.preventDefault();
        toggleFullscreen();
    }
});

</script>

@endsection