<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Overlay Juego - Cuanto Sabe</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@700&family=Rajdhani:wght@700&display=swap" rel="stylesheet">
 <style>
    html, body {
        width: 100vw; height: 100vh; margin: 0; padding: 0;
        overflow: hidden; background: transparent !important;
    }
    body {
        font-family: 'Orbitron', Arial, sans-serif; color: #fff;
        display: flex; flex-direction: column; align-items: center; justify-content: flex-end;
        min-height: 100vh; width: 100vw;
    }
    .connection-status {
        position: fixed; top: 20px; right: 20px; width: 12px; height: 12px; border-radius: 50%;
        background: #ff3f34; z-index: 999; transition: background 0.3s;
    }
    .connection-status.connected { background: #13ff79; }
    .overlay-content {
        width: 100vw; max-width: 1200px;
        margin-bottom: 3vh; display: flex; flex-direction: column; align-items: center; z-index: 20;
    }
    .question-bar, .answers-row {
        width: 100%; max-width: 1200px;
    }
    .question-bar {
        min-height: 52px; margin: 0 auto 2vh auto;
        background: linear-gradient(90deg, #111b2b 80%, #004563 100%);
        border-radius: 28px; display: flex; align-items: center; justify-content: center;
        font-size: 1.74rem; font-weight: 700; text-align: center; color: #fff;
        text-shadow: 0 0 10px #00f0ff; box-shadow: 0 0 15px #19faffb9, 0 0 1px #fff6;
        border: none; letter-spacing: 1.2px; font-family: 'Orbitron', Arial, sans-serif;
        transition: box-shadow 0.25s;
    }
    .answers-row {
        display: grid; grid-template-columns: 1fr 1fr; gap: 16px 28px; margin-bottom: 1.3vh;
    }
    .option-box {
        display: flex; align-items: center; justify-content: flex-start;
        font-size: 1.32rem; background: linear-gradient(90deg, #0b1530 75%, #12375c 100%);
        color: #fff; padding: 16px 28px 16px 28px; border-radius: 23px;
        box-shadow: 0 0 14px #19faffaa, 0 0 1px #fff8;
        font-family: 'Orbitron', Arial, sans-serif; font-weight: 600; position: relative; border: none;
        min-height: 46px; transition: background 0.12s, box-shadow 0.14s, color 0.15s, transform 0.16s; letter-spacing: 1px;
    }
    .option-box .opt-label {
        font-size: 1.62rem; color: #36ffd0; margin-right: 19px; font-weight: 900; text-shadow: 0 0 8px #1affd2b5;
    }
    .option-box .opt-text { flex: 1; font-size: 1.04em; }
    .option-box .total-votes {
        font-size: 1.01rem; color: #b7eaff; position: absolute; right: 22px; bottom: 8px; font-weight: 400; text-shadow: 0 0 5px #00f0ff8c;
    }
    .option-box.selected, .option-box:hover {
        background: linear-gradient(90deg, #ffe47a 80%, #e6be2f 100%); color: #333; transform: scale(1.07);
        box-shadow: 0 0 22px #ffe47a99, 0 0 5px #e6be2f77; z-index: 1;
    }
    @keyframes flash-green {0%, 100%{background:linear-gradient(90deg,#0b1530 75%,#12375c 100%);color:#fff;}25%,75%{background:linear-gradient(90deg,#13ff79 70%,#07ce5e 110%);color:#fff;}50%{background:linear-gradient(90deg,#0b1530 75%,#12375c 100%);color:#fff;}}
    .option-box.correct-flash {animation:flash-green 0.65s 2;}
    .option-box.correct-final {background:linear-gradient(90deg,#13ff79 80%,#07ce5e 100%);color:#003e18;box-shadow:0 0 33px #15ff99c9,0 0 5px #07ce5e99;}
    @keyframes flash-red {0%,100%{background:linear-gradient(90deg,#0b1530 75%,#12375c 100%);color:#fff;}25%,75%{background:linear-gradient(90deg,#ff3f34 80%,#d00015 100%);color:#fff;}50%{background:linear-gradient(90deg,#0b1530 75%,#12375c 100%);color:#fff;}}
    .option-box.incorrect-flash {animation:flash-red 0.65s 2;}
    .option-box.incorrect-final {background:linear-gradient(90deg,#ff3f34 80%,#d00015 100%);color:#fff;box-shadow:0 0 26px #ff4a4a99;}
    #ruleta-container {
        position: relative; width: 440px; height: 440px; margin-bottom: 3vh;
    }
    #ruleta-svg {
        width: 440px; height: 440px; display: block;
        filter: drop-shadow(0 0 18px #0e1528ee);
    }
    #flecha-roja {
        position: absolute; background: transparent;
        left: 50%; top: -10px; transform: translateX(-50%);
        z-index: 20; width: 81.6px; height: 57.8px; pointer-events: none;
    }
    #spin-btn {
        position: absolute; left: 50%; top: 50%;
        transform: translate(-50%, -50%);
        width: 85px; height: 85px; border-radius: 50%;
        border: 2.7px solid #0e1528ee; background:rgb(12, 42, 74);
        z-index: 21; padding: 0; display: flex; align-items: center; justify-content: center;
        box-shadow:
            0 0 26px 9px #2d60a666,
            0 7px 20px #181b3f88,
            0 1px 3px #181b3f66,
            inset 0 0 0 1.2px #153364;
        cursor: pointer; transition: box-shadow 0.28s, background 0.18s;
    }
    #spin-btn:hover {
        box-shadow:
            0 0 38px 14px #2987d888,
            0 0 17px #00f0ff,
            0 0 13px #ffe47a44,
            0 6px 20px #0a132bcc;
        background: #205093;
    }
    #spin-btn img {
        width: 100%; height: 100%; border-radius: 50%; object-fit: contain;
        background: #181b3f; border: none; box-shadow: none; display: block; padding: 0;
    }
    /* Overlay y ruleta: estado oculto y visible con animación */
    .overlay-content, #ruleta-container {
        opacity: 1;
        transform: translateY(0);
        transition: opacity 0.55s cubic-bezier(.39,.58,.57,1.02), transform 0.55s cubic-bezier(.39,.58,.57,1.02);
        will-change: opacity, transform;
    }
    .overlay-content.hide-down { opacity: 0; transform: translateY(80px); pointer-events: none;}
    .overlay-content.show-up { opacity: 1; transform: translateY(0);}
    #ruleta-container.hide-up { opacity: 0; transform: translateY(-90px); pointer-events: none;}
    #ruleta-container.show-down { opacity: 1; transform: translateY(0);}
    .overlay-content.hide-down {
        opacity: 0;
        transform: translateY(60px);
        pointer-events: none;
    }
    .overlay-content.show-up {
        opacity: 1;
        transform: translateY(0);
    }
    #ruleta-container.hide-up {
        opacity: 0;
        transform: translateY(-70px);
        pointer-events: none;
    }
    #ruleta-container.show-down {
        opacity: 1;
        transform: translateY(0);
    }
    .overlay-content {
        position: relative; /* ¡Esto es clave para el banner absoluto! */
    }
    /* TENDENCIA */
    .option-box.tendencia {
        box-shadow: 0 0 38px #22fa68cc, 0 0 15px #fff7;
        border: 2.5px solid #22fa68;
        position: relative;
    }
    .option-box.tendencia::after {
        content: 'TENDENCIA';
        position: absolute;
        top: -16px;
        right: 22px;
        background: linear-gradient(90deg, #22fa68 60%, #19faff 100%);
        color: #122a16;
        font-family: 'Orbitron', Arial, sans-serif;
        font-weight: 900;
        padding: 2px 12px;
        border-radius: 11px;
        font-size: 0.99rem;
        box-shadow: 0 0 8px #19faffaa;
        letter-spacing: 1px;
        z-index: 2;
        border: none;
    }

    /* ------ TOP BAR: para dejar SIEMPRE el puntaje a la derecha ------ */
   .top-bar-row {
    width: 100%;
    max-width: 1200px;
    height: 58px; /* Alto fijo real, no min-height! */
    margin: 0 auto 18px auto;
    border-radius: 28px;
    display: flex;
    flex-direction: row;
    align-items: center;  /* Cambia stretch por center */
    justify-content: space-between;
    background: transparent;
    box-shadow: none;
    padding: 0;
}

.banner-holder {
    min-width: 180px;
    max-width: 390px;
    width: auto;
    display: flex;
    align-items: center;
    justify-content: flex-start;
    /* Sin flex-basis fijo */
}

#indicator-banner {
    width: 100%;
    min-width: 140px;
    max-width: 390px;
    height: 52px;
    display: flex;
    align-items: center;
    padding: 0 22px;
    border-radius: 22px;
    font-size: 1.16rem;
    font-family: 'Orbitron', Arial, sans-serif;
    font-weight: 800;
    letter-spacing: 1.6px;
    text-align: left;
    box-shadow: 0 0 15px #19faffb9, 0 0 1px #fff6;
    border: none;
    background: linear-gradient(90deg,#101d2b 85%,#0b2845 100%);
    color: #34faff;
    text-shadow:
        0 0 13px #00fff799,
        0 0 8px #0ff0fc77,
        0 0 3px #fff8,
        0 2px 2px #011;
    transition: background 0.25s, color 0.25s, box-shadow 0.25s;
}
#indicator-banner.banner-oro,
#indicator-banner.banner-verde,
#indicator-banner.banner-azul {
    position: relative;
    overflow: hidden;
    /* Efecto de borde brillante: */
    box-shadow:
      0 0 22px 1.5px rgba(255,228,122,0.45),
      0 0 8px 1.5px rgba(255,255,255,0.16),
      0 0 1.5px 0px #000;
}

#indicator-banner.banner-oro {
    background: radial-gradient(circle at 58% 33%, #786200 0%, #312407 54%, #221b05 100%);
    color: #ffe47a;
    border: 2.2px solid #ffe47a;
    box-shadow:
      0 0 19px 2px #ffe47a99,
      0 0 0.5px #fff3,
      0 0 0px #000;
    text-shadow: 0 0 6px #ffe47a, 0 2px 2px #191400;
}

#indicator-banner.banner-verde {
    background: radial-gradient(circle at 62% 30%, #1ad964 0%, #17462b 54%, #0b1c12 100%);
    color: #22fa68;
    border: 2.2px solid #22fa68;
    box-shadow:
      0 0 17px 2px #22fa6888,
      0 0 0.5px #fff2,
      0 0 0px #000;
    text-shadow: 0 0 6px #36ffd0bb, 0 2px 2px #011;
}

#indicator-banner.banner-azul {
    background: radial-gradient(circle at 65% 37%, #2987d8 0%, #182b3e 54%, #0a1628 100%);
    color: #36d1ff;
    border: 2.2px solid #36d1ff;
    box-shadow:
      0 0 19px 2px #36d1ff88,
      0 0 0.5px #fff2,
      0 0 0px #000;
    text-shadow: 0 0 7px #19faffaa, 0 2px 2px #012;
}

/* Highlight superior muy sutil */
#indicator-banner.banner-oro::before,
#indicator-banner.banner-verde::before,
#indicator-banner.banner-azul::before {
    content: "";
    position: absolute;
    left: 12%; top: 0;
    width: 75%;
    height: 34%;
    border-radius: 50% 50% 20% 20% / 55% 55% 16% 16%;
    background: linear-gradient(to bottom,
        rgba(255,255,255,0.15) 0%,
        rgba(255,255,255,0.03) 80%,
        rgba(255,255,255,0.00) 100%);
    opacity: 0.19;
    z-index: 2;
    pointer-events: none;
    filter: blur(0.5px);
}

#indicator-banner.banner-oro::after,
#indicator-banner.banner-verde::after,
#indicator-banner.banner-azul::after {
    display: none; /* Eliminamos reflejo secundario */
}

#indicator-banner:empty { visibility: hidden; }

.guest-points-bar {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    height: 52px;
    background: linear-gradient(90deg, #111b2b 80%, #004563 100%);
    border-radius: 22px;
    box-shadow: 0 0 15px #19faffb9, 0 0 1px #fff6;
    padding: 0 22px;
    min-width: 140px;
    max-width: 260px;
    margin-left: 10px;
    font-family: 'Orbitron', Arial, sans-serif;
    font-weight: 500;
    font-size: 1.74rem; /* ¡Valor grande! */
    color: #fff;
    text-shadow: 0 0 10px #00f0ff;
    letter-spacing: 1.1px;
    border: none;
}

.guest-points-bar .gp-title {
    font-size: 1.68rem;  /* 50-60% más chico que el valor */
    font-weight: 700;
    margin-right: 9px;
    margin-left: -5px;
    color: #fff;            /* Igual que question-bar */
    text-shadow: 0 0 10px #00f0ff;  /* Igual que question-bar */
    /*opacity: 0.74;*/
    letter-spacing: 0.5px;
}

.guest-points-bar .gp-value {
    color: #ffe47a;
    font-size: 1.78rem;  /* Valor grande */
    font-weight: 900;
    margin-left: 3px;
    text-shadow: 0 0 13px #ffe47a99, 0 0 4px #fff7;
}
.indicator-banner {
    position: fixed;
    top: 20px;
    left: 20px;
    padding: 10px 20px;
    font-weight: bold;
    font-family: sans-serif;
    border-radius: 8px;
    border: 2.2px solid;
    text-shadow: 0 0 7px #19faffaa, 0 2px 2px #012;
    box-shadow: 0 0 19px 2px #36d1ff88, 0 0 0.5px #fff2, 0 0 0px #000;
    transition: all 0.3s ease-in-out;
    z-index: 9999;
}

/* Banners distintos */
#apuesta-indicator {
    background: radial-gradient(circle at 65% 37%, #2987d8 0%, #182b3e 54%, #0a1628 100%);
    color: #36d1ff;
    border-color: #36d1ff;
}

#descarta-indicator {
    background: radial-gradient(circle at 65% 37%, #d82c2c 0%, #3e1818 54%, #280a0a 100%);
    color: #ff3636;
    border-color: #ff3636;
}


</style>

<div style="display: flex; gap: 10px; justify-content: flex-start;">
    <div id="descarta-indicator" class="indicator-banner" style="display: none;">
        DESCARTE USADO
    </div>

    <div id="apuesta-indicator" class="indicator-banner" style="display: none;">
        APUESTA USADA
    </div>
</div>


</head>
<body>
    <div class="connection-status" id="connectionStatus"></div>
<div class="overlay-content">
  <div class="top-bar-row">
    <div class="banner-holder">
      <div id="indicator-banner"></div>
    </div>
<div class="guest-points-bar" id="categoriaBar" style="display: none;">
  <span class="gp-value" id="categoriaValue">-</span>
</div>
  </div>
  <div class="question-bar" id="questionBar">Esperando pregunta...</div>
        <div class="answers-row">
            <div class="option-box" id="opA">
                <span class="opt-label">A</span>
                <span class="opt-text">Opción A</span>
                <span class="total-votes"><span class="vote-count"></span></span>
            </div>
            <div class="option-box" id="opB">
                <span class="opt-label">B</span>
                <span class="opt-text">Opción B</span>
                <span class="total-votes"><span class="vote-count"></span></span>
            </div>
        </div>
        <div class="answers-row">
            <div class="option-box" id="opC">
                <span class="opt-label">C</span>
                <span class="opt-text">Opción C</span>
                <span class="total-votes"><span class="vote-count"></span></span>
            </div>
            <div class="option-box" id="opD">
                <span class="opt-label">D</span>
                <span class="opt-text">Opción D</span>
            <span class="total-votes"><span class="vote-count"></span></span>
            </div>
        </div>
    </div>
    <div id="ruleta-container">
        <svg id="flecha-roja" viewBox="0 0 90 65">
            <polygon points="0,0 90,0 45,65"
                style="fill:rgba(255,60,60,0.93);stroke:#ff4747;stroke-width:7;" />
        </svg>
        <svg id="ruleta-svg" width="440" height="440"></svg>
        <button id="spin-btn" title="Girar">
            <img src="{{ asset('images/ruleta.png') }}"
                alt="Logo Ruleta"
                onerror="this.onerror=null;this.src='https://upload.wikimedia.org/wikipedia/commons/9/9a/Circle-icons-profile.svg'">
        </button>
    </div>
    <audio id="rightSound" src="/sounds/right.mp3" preload="auto"></audio>
    <audio id="wrongSound" src="/sounds/wrong.mp3" preload="auto"></audio>
    <script>
window.sessionGame = @json($sessionGame);
    </script>
    <script src="https://js.pusher.com/7.2/pusher.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/laravel-echo/1.11.3/echo.iife.js"></script>
    <script>
// ===== INICIALIZACIÓN PUSHER/ECHO (solo 1 vez) =====
window.Pusher = Pusher;
window.Echo = new Echo({
    broadcaster: 'pusher',
    key: "{{ config('broadcasting.connections.pusher.key') }}",
    cluster: "{{ config('broadcasting.connections.pusher.options.cluster') }}",
    forceTLS: true,
});

// Estado conexión visual
function updateConnectionStatus(connected) {
    document.getElementById('connectionStatus').classList.toggle('connected', connected);
}
window.Echo.connector.pusher.connection.bind('connected', () => updateConnectionStatus(true));
window.Echo.connector.pusher.connection.bind('disconnected', () => updateConnectionStatus(false));

// =========== PANEL LOGIC =============
let pendingSpecialBanner = null; // <-- Banner especial pendiente
let currentOptions = [];
let correctLabel = null;
let ultimaSeleccionPanel = null;
const questionBar = document.getElementById('questionBar');
const options = ['A', 'B', 'C', 'D'];

let isFetching = false;
let lastFetch = 0;
const FETCH_COOLDOWN = 2000; // 2 segundos mínimo entre llamadas

async function fetchOverlayState() {
    const now = Date.now();
    
    // 🔥 Evitar llamadas duplicadas
    if (isFetching || (now - lastFetch) < FETCH_COOLDOWN) {
        console.log('[DEBUG] fetchOverlayState: cooldown activo, skip');
        return;
    }
    
    isFetching = true;
    lastFetch = now;
    
    try {
        // 1. Pregunta activa
        let pregunta = null;
        const resP = await fetch('/overlay/api/pregunta');
        if (resP.ok) pregunta = await resP.json();

        if (pregunta && pregunta.pregunta) {
            showQuestion(pregunta);
        } else {
            resetOverlay();
        }

        /*
        let puntos = 0;
        const resPts = await fetch('/overlay/api/puntos');
        if (resPts.ok) {
            const json = await resPts.json();
            puntos = json.points ?? 0;
        }
        const val = document.getElementById('guestPointsValue');
        if (val) val.textContent = puntos;
        */
        
    } catch (e) {
        console.error('[ERROR] fetchOverlayState:', e);
    } finally {
        isFetching = false;
    }
}
function updateCategory(categoryName) {
    const categoryEl = document.getElementById('categoryValue');
    if (categoryEl) {
        categoryEl.textContent = categoryName ? categoryName.toUpperCase() : '-';
    }
}

// Helper para animación crossfade (llama a callback opcional al terminar)
function toggleAnim(el, showClass, hideClass, mostrar, cb) {
    if(mostrar) {
        el.style.display = '';
        setTimeout(() => {
            el.classList.add(showClass);
            el.classList.remove(hideClass);
        }, 10);
        if(cb) setTimeout(cb, 550);
    } else {
        el.classList.add(hideClass);
        el.classList.remove(showClass);
        setTimeout(() => {
            el.style.display = 'none';
            if(cb) cb();
        }, 550);
    }
}

function resetOverlay() {
        // 🔥 LIMPIA TENDENCIA DE TODAS LAS OPCIONES
    ['A','B','C','D'].forEach(l => {
        const optEl = document.getElementById('op'+l);
        optEl && optEl.classList.remove('tendencia');
    });
    questionBar.textContent = 'Esperando pregunta...';
    const banner = document.getElementById('indicator-banner');
    banner.textContent = '';
    banner.style.display = 'none';
    banner.classList.remove('banner-oro', 'banner-verde', 'banner-azul');
    pendingSpecialBanner = null; // ⚡️ LIMPIA TODO por si acaso
    currentOptions = [];
    correctLabel = null;
    ultimaSeleccionPanel = null;
    options.forEach(opt => {
        const optEl = document.getElementById('op' + opt);
        optEl.classList.remove('selected', 'correct-flash', 'correct-final', 'incorrect-flash', 'incorrect-final');
        optEl.querySelector('.opt-text').textContent = 'Opción ' + opt;
     //   optEl.querySelector('.vote-count').textContent = '0';
        optEl.style.display = 'flex';
    });
    const ruleta = document.getElementById('ruleta-container');
    const overlay = document.querySelector('.overlay-content');
    toggleAnim(overlay, 'show-up', 'hide-down', false, () => {
        toggleAnim(ruleta, 'show-down', 'hide-up', true);
    });
    updateCategory(null);
}


function showQuestion(data) {
    // 🔥 LIMPIA TENDENCIA DE TODAS LAS OPCIONES
    ['A','B','C','D'].forEach(l => {
        const optEl = document.getElementById('op'+l);
        optEl && optEl.classList.remove('tendencia');
    });

    window.lastQuestionData = data;
    console.log('[DEBUG] showQuestion data:', data);
    console.log('[DEBUG] categoria_nombre:', data.categoria_nombre);
    console.log('[DEBUG] special_indicator:', data.special_indicator);

    // ✅ ACTUALIZAR VALOR DE CATEGORÍA PERO MANTENER OCULTA
    const categoriaBar = document.getElementById('categoriaBar');
    const categoriaValue = document.getElementById('categoriaValue');
    
    if (data.categoria_nombre) {
        if (categoriaValue) {
            categoriaValue.textContent = data.categoria_nombre.toUpperCase();
        }
        // ❌ NO mostrar todavía, esperar los 10 segundos
        if (categoriaBar) {
            categoriaBar.style.display = 'none';
        }
        console.log('[DEBUG] Categoría actualizada (oculta):', data.categoria_nombre);
    }

    // --- BANNER ESPECIAL ---
    const banner = document.getElementById('indicator-banner');
    let indicator = data.special_indicator;

    // SI NO VIENE DE BACKEND, PERO HAY UN SPECIAL PENDIENTE, USALO (SOLO UNA VEZ):
    if (!indicator && pendingSpecialBanner) {
        indicator = pendingSpecialBanner;
        pendingSpecialBanner = null;
        console.log('[DEBUG] Usando pendingSpecialBanner:', indicator);
    } else {
        pendingSpecialBanner = null;
    }

    // --- LIMPIA CLASES DE COLOR ANTERIORES ---
    banner.classList.remove('banner-oro', 'banner-verde', 'banner-azul');

    // --- MOSTRAR U OCULTAR EL BANNER DE INDICADOR ESPECIAL ---
    if (!indicator) {
        banner.textContent = '';
        banner.style.display = 'none';
    } else {
        banner.textContent = indicator.toUpperCase();
        banner.style.display = '';
        let tipo = indicator.trim().toLowerCase();
        if (tipo === 'pregunta de oro') banner.classList.add('banner-oro');
        else if (tipo === 'solo yo') banner.classList.add('banner-verde');
        else if (tipo === 'responde el chat' || tipo === 'solo chat') banner.classList.add('banner-azul');
    }

    currentOptions = data.opciones || [];
    correctLabel = data.label_correcto || data.opcion_correcta || null;
    ultimaSeleccionPanel = null;

    // ✅ PRIMERO: Mostrar solo la categoría en el question-bar
    const categoria = data.categoria_nombre ? data.categoria_nombre.toUpperCase() : 'CATEGORÍA';
    questionBar.textContent = categoria;

    // Ocultar todas las opciones inicialmente
    options.forEach(opt => {
        const optEl = document.getElementById('op' + opt);
        optEl.style.display = 'none';
        optEl.classList.remove('selected', 'correct-flash', 'correct-final', 'incorrect-flash', 'incorrect-final');
    });

    // Mostrar el overlay con animación
    const ruleta = document.getElementById('ruleta-container');
    const overlay = document.querySelector('.overlay-content');
    toggleAnim(ruleta, 'show-down', 'hide-up', false, () => {
        toggleAnim(overlay, 'show-up', 'hide-down', true);
    });

    // ✅ DESPUÉS DE 10 SEGUNDOS: Mostrar pregunta, opciones Y categoriaBar
    setTimeout(() => {
        questionBar.textContent = data.pregunta || 'Pregunta sin texto';

        // ✅ MOSTRAR la barra de categoría ahora
        if (categoriaBar) {
            categoriaBar.style.display = 'flex';
        }

        options.forEach(opt => {
            const optEl = document.getElementById('op' + opt);
            const optData = currentOptions.find(o => o.label === opt);
            if (optData) {
                optEl.querySelector('.opt-text').textContent = optData.texto;
                optEl.style.display = 'flex';
            } else {
                optEl.style.display = 'none';
            }
        });
    }, 10000); // 10 segundos
}

// NUEVO: Ruleta se va tras seleccionar opción (llamalo desde showSelectedOption)
let ruletaOculta = false;
function showSelectedOption(option) {
    options.forEach(opt => document.getElementById('op' + opt).classList.remove('selected'));
    if (option) {
        const optEl = document.getElementById('op' + option);
        if (optEl) optEl.classList.add('selected');
    }
    // Ocultar ruleta tras 1s SOLO la primera vez tras seleccionar
    if(!ruletaOculta) {
        ruletaOculta = true;
        setTimeout(() => {
            const ruleta = document.getElementById('ruleta-container');
            toggleAnim(ruleta, 'show-down', 'hide-up', false);
        }, 1000);
    }
}

// Cuando reseteás o se muestra la ruleta de nuevo, volver a habilitar
function resetRuletaAnim() { ruletaOculta = false; }
window.resetRuletaAnim = resetRuletaAnim;

// Llamalo después de resetOverlay para poder repetir animación si querés
// resetOverlay(); resetRuletaAnim();


function playSound(id) {
    const audio = document.getElementById(id);
    if (!audio) return;
    audio.currentTime = 0;
    audio.play().catch(()=>{});
}

function revealAnswer(data) {
    // Usar los datos del backend o el estado actual (usa los que vengan en data)
    const opciones = data.opciones || currentOptions;
    const labelCorrecto = data.label_correcto || data.opcion_correcta || correctLabel;
    let incorrectaMarcada = false;
    opciones.forEach(op => {
        const l = op.label;
        const optEl = document.getElementById('op' + l);
        if (!optEl || optEl.style.display === 'none') return;
        optEl.classList.remove('correct-flash', 'correct-final', 'incorrect-flash', 'incorrect-final');
        if (l === ultimaSeleccionPanel && l !== labelCorrecto) {
            incorrectaMarcada = true;
            optEl.classList.add('incorrect-flash');
            playSound('wrongSound');
            setTimeout(() => {
                optEl.classList.remove('incorrect-flash');
                optEl.classList.add('incorrect-final');
            }, 1200);
        }
        if (l === labelCorrecto && l === ultimaSeleccionPanel) {
            optEl.classList.add('correct-flash');
            playSound('rightSound');
            setTimeout(() => {
                optEl.classList.remove('correct-flash');
                optEl.classList.add('correct-final');
            }, 1200);
        }
    });
    if (incorrectaMarcada && labelCorrecto) {
        setTimeout(() => {
            const correctOpt = document.getElementById('op' + labelCorrecto);
            if (correctOpt) {
                correctOpt.classList.add('correct-flash');
                setTimeout(() => {
                    correctOpt.classList.remove('correct-flash');
                    correctOpt.classList.add('correct-final');
                }, 1200);
            }
        }, 5000);
    }
    if (!incorrectaMarcada && labelCorrecto && ultimaSeleccionPanel !== labelCorrecto) {
        const correctOpt = document.getElementById('op' + labelCorrecto);
        if (correctOpt) {
            correctOpt.classList.add('correct-flash');
            setTimeout(() => {
                correctOpt.classList.remove('correct-flash');
                correctOpt.classList.add('correct-final');
            }, 1200);
        }
    }
}

window.Echo.channel('overlay-channel')
    .listen('.girar-ruleta', () => {
        window.girarRuletaRemoto && window.girarRuletaRemoto();
    })
    .listen('.nueva-pregunta', e => {
        if (!e.data || !e.data.pregunta) {
            fetchOverlayState(); // 🚨 Si el evento viene vacío, refrescá del backend
        } else {
            showQuestion(e.data || e);
        }
    })
.listen('.GameBonusUpdated', (event) => {
        console.log('[BONUS] Evento recibido:', event);
        
        // Mostrar/ocultar indicador de Apuesta x2
        const apuestaIndicator = document.getElementById('apuesta-indicator');
        if (event.apuesta_x2_active) {
            apuestaIndicator.style.display = 'block';
        } else {
            apuestaIndicator.style.display = 'none';
        }
        
        // Mostrar/ocultar indicador de Descarte
        const descarteIndicator = document.getElementById('descarta-indicator');
        if (event.descarte_usados > 0) {
            descarteIndicator.style.display = 'block';
        } else {
            descarteIndicator.style.display = 'none';
        }
    })
    .listen('.opcion-seleccionada', e => {
        ultimaSeleccionPanel = e.opcion;
        showSelectedOption(e.opcion);
    })
    .listen('.revelar-respuesta', e => {
        revealAnswer(e.data || e);
    })
    .listen('.overlay-reset', () => {
        resetOverlay();
        fetchOverlayState(); // 🚨 Tras un reset, asegurate que la UI quede sincronizada
    })
    .listen('.tendencia-actualizada', e => {
        ['A','B','C','D'].forEach(l => {
            const optEl = document.getElementById('op'+l);
            optEl && optEl.classList.remove('tendencia');
        });
        if (e.data && e.data.option_label) {
            const tendenciaEl = document.getElementById('op' + e.data.option_label);
            if (tendenciaEl) {
                tendenciaEl.classList.add('tendencia');
            }
        }
    });
    /*
    .listen('.GuestPointsUpdated', e => {
        const bar = document.getElementById('guestPointsBar');
        const val = document.getElementById('guestPointsValue');
        if (bar && val) {
            val.textContent = e.points;
            bar.style.display = '';
        }
    });
    */


// ---- Inicial ----
resetOverlay();
    </script>
    <script>
/* === RULETA JS AQUÍ: SIN CAMBIOS, tu lógica igual === */

// ---- CONFIGURACIÓN DE RULETA ----
window.sessionGame = window.sessionGame || { categories: [] };

// Si necesitas simular categorías en local descomenta esto:
// window.sessionGame = { categories: [
//     {label: "Pregunta de oro", fixed: true},
//     {label: "Random", fixed: true},
//     {label: "Solo chat", fixed: true},
//     {label: "Solo yo", fixed: true},
//     {label: "Historia"}, {label: "Ciencia"}, {label: "Arte"}, {label: "Cine"}
// ]};

const colorBase = "#153364";      // Azul base slot
const borderNeon = "#2d60a6";    // Azul vibrante bordes
const borderShadow = "#193a68";  // Sombra azul exterior
const slotShadow = "#070c16f6";  // Sombra oscura entre slots
const winnerColor = "#2987d8";   // Slot destacado
const innerDarkStroke = "#111927"; // Borde oscuro entre slots
const neonGreen = "#22fa68";     // Verde neón elegante
const neonGreenText = "#eaffdb"; // Texto claro para verde

const categories = (window.sessionGame && window.sessionGame.categories) ? window.sessionGame.categories : [];
const fixedTypes = ["pregunta de oro", "responde el chat", "solo yo", "random"];
const sizePresets = {
    "pregunta de oro": 0.02,
    "responde el chat": 0.08,
    "solo yo": 0.08,
    "random": 0.27
};

const slots = categories.map(cat => ({
    label: cat.label,
    color: cat.fixed
        ? (cat.color || winnerColor)
        : neonGreen,
    textColor: cat.fixed
        ? (cat.textColor || "#99e6ff")
        : neonGreenText,
    size: null,
    type: cat.fixed ? cat.label.toLowerCase().replace(/\s/g,'') : "cat"
}));

let slotsSum = 0;
slots.forEach(s => {
    if (fixedTypes.includes(s.label.toLowerCase())) {
        s.size = sizePresets[s.label.toLowerCase()];
        slotsSum += s.size;
    }
});
let regularSlots = slots.filter(s => !s.size);
let eachRegular = (1 - slotsSum) / (regularSlots.length || 1);
slots.forEach(s => { if(!s.size) s.size = eachRegular; });

const svg = document.getElementById('ruleta-svg');
const W = 440, H = 440, CX = W / 2, CY = H / 2, R = 205, R2 = 189;
let currentAngle = 0;
let selectedSlotIdx = null;
let spinning = false;
let stopRequested = false;
let spinAnimation = null;

function easeOutBack(x) {
    const c1 = 1.70158 * 1.12;
    const c3 = c1 + 1.2;
    return 1 + c3 * Math.pow(x - 1, 3) + c1 * Math.pow(x - 1, 2);
}

function drawRuleta(angleBase = 0, selectedIdx = null, highlightT = 0) {
    svg.innerHTML = `
      <defs>
        <radialGradient id="bgGradient" cx="50%" cy="50%" r="63%">
          <stop offset="0%" stop-color="#232946"/>
          <stop offset="100%" stop-color="#111b2b"/>
        </radialGradient>
        <linearGradient id="slotReflex" x1="20%" y1="10%" x2="90%" y2="80%">
          <stop offset="0%" stop-color="#fffde7" stop-opacity="0.32"/>
          <stop offset="0.28" stop-color="#ffe47a" stop-opacity="0.15"/>
          <stop offset="0.66" stop-color="#fffde7" stop-opacity="0.09"/>
          <stop offset="1" stop-color="#ffe47a" stop-opacity="0.01"/>
        </linearGradient>
        <filter id="neonBorder" x="-20%" y="-20%" width="140%" height="140%">
          <feGaussianBlur stdDeviation="4" result="glow"/>
          <feMerge>
            <feMergeNode in="glow"/>
            <feMergeNode in="SourceGraphic"/>
          </feMerge>
        </filter>
        <filter id="slotRelief" x="-10%" y="-10%" width="120%" height="120%">
          <feDropShadow dx="0" dy="2" stdDeviation="1.3" flood-color="#151c2b" flood-opacity="0.22"/>
        </filter>
        <filter id="slotGlow" x="-10%" y="-10%" width="120%" height="120%">
          <feDropShadow dx="0" dy="0" stdDeviation="3" flood-color="${borderShadow}" flood-opacity="0.31"/>
        </filter>
        <filter id="goldGlow" x="-25%" y="-25%" width="150%" height="150%">
          <feGaussianBlur stdDeviation="6" result="glow"/>
          <feMerge>
            <feMergeNode in="glow"/>
            <feMergeNode in="SourceGraphic"/>
          </feMerge>
        </filter>
        <filter id="slotShadow" x="-16%" y="-16%" width="120%" height="120%">
          <feDropShadow dx="0" dy="0" stdDeviation="2.4" flood-color="${slotShadow}" flood-opacity="0.85"/>
        </filter>
      </defs>
    `;

    // Fondo central
    let borderCircle = document.createElementNS("http://www.w3.org/2000/svg", "circle");
    borderCircle.setAttribute("cx", CX);
    borderCircle.setAttribute("cy", CY);
    borderCircle.setAttribute("r", R-3);
    borderCircle.setAttribute("fill", "url(#bgGradient)");
    borderCircle.setAttribute("stroke", borderNeon);
    borderCircle.setAttribute("stroke-width", "2.1");
    borderCircle.setAttribute("filter", "url(#neonBorder)");
    svg.appendChild(borderCircle);

    // Marcos entre slots
    let a0 = angleBase;
    slots.forEach((s, idx) => {
        let ang = s.size * 2 * Math.PI;
        let x1 = CX + R2 * Math.cos(a0);
        let y1 = CY + R2 * Math.sin(a0);
        let x2 = CX + R2 * Math.cos(a0 + ang);
        let y2 = CY + R2 * Math.sin(a0 + ang);
        let largeArc = ang > Math.PI ? 1 : 0;
        let pathData = `
            M ${CX} ${CY}
            L ${x1} ${y1}
            A ${R2} ${R2} 0 ${largeArc} 1 ${x2} ${y2}
            Z
        `;

        let borderPath = document.createElementNS("http://www.w3.org/2000/svg", "path");
        borderPath.setAttribute("d", pathData);
        borderPath.setAttribute("fill", "none");
        borderPath.setAttribute("stroke", borderNeon);
        borderPath.setAttribute("stroke-width", "2.2");
        borderPath.setAttribute("filter", "url(#slotGlow)");
        svg.appendChild(borderPath);

        let innerBorderPath = document.createElementNS("http://www.w3.org/2000/svg", "path");
        innerBorderPath.setAttribute("d", pathData);
        innerBorderPath.setAttribute("fill", "none");
        innerBorderPath.setAttribute("stroke", innerDarkStroke);
        innerBorderPath.setAttribute("stroke-width", "0.9");
        innerBorderPath.setAttribute("opacity", "0.72");
        svg.appendChild(innerBorderPath);

        a0 += ang;
    });

    // SLOTS
    a0 = angleBase;
    let slotDataList = [];
    slots.forEach((s, idx) => {
        let ang = s.size * 2 * Math.PI;
        let midAngle = a0 + ang / 2;
        let isWinner = (selectedIdx !== null && idx === selectedIdx);
        let slotR2 = R2;
        let maxScale = 1.17, minScale = 1.08;
        let extra = 0;
        if(isWinner && highlightT > 0) {
            let scale = minScale + (maxScale-minScale)*easeOutBack(highlightT);
            slotR2 = R2 * scale;
            extra = scale - 1;
        } else if (isWinner) {
            slotR2 = R2 * minScale;
            extra = minScale - 1;
        }

        let x1w = CX + slotR2 * Math.cos(a0);
        let y1w = CY + slotR2 * Math.sin(a0);
        let x2w = CX + slotR2 * Math.cos(a0 + ang);
        let y2w = CY + slotR2 * Math.sin(a0 + ang);
        let largeArc = ang > Math.PI ? 1 : 0;
        let pathDataWinner = `
            M ${CX} ${CY}
            L ${x1w} ${y1w}
            A ${slotR2} ${slotR2} 0 ${largeArc} 1 ${x2w} ${y2w}
            Z
        `;

        let fillColor = isWinner ? s.color : colorBase;
        let filter = "url(#slotRelief) url(#slotShadow)";
        if(isWinner) {
            filter = s.type === "preguntadeoro"
                ? "url(#goldGlow) url(#slotGlow) url(#slotShadow)"
                : "url(#slotGlow) url(#slotShadow)";
        }

        let slotPath = document.createElementNS("http://www.w3.org/2000/svg", "path");
        slotPath.setAttribute("d", isWinner ? pathDataWinner : `
            M ${CX} ${CY}
            L ${CX + R2 * Math.cos(a0)} ${CY + R2 * Math.sin(a0)}
            A ${R2} ${R2} 0 ${largeArc} 1 ${CX + R2 * Math.cos(a0 + ang)} ${CY + R2 * Math.sin(a0 + ang)}
            Z
        `);
        slotPath.setAttribute("fill", fillColor);
        slotPath.setAttribute("stroke", "none");
        slotPath.setAttribute("filter", filter);
        svg.appendChild(slotPath);

        // Reflejo slot seleccionado
        if(isWinner){
            let reflexPath = document.createElementNS("http://www.w3.org/2000/svg", "path");
            reflexPath.setAttribute("d", pathDataWinner);
            reflexPath.setAttribute("fill", "url(#slotReflex)");
            reflexPath.setAttribute("opacity", "0.55");
            svg.appendChild(reflexPath);
        }

        slotDataList.push({s, idx, midAngle, slotR2, isWinner, extra});
        a0 += ang;
    });

    // TEXTO RADIAL
    slotDataList.forEach(({s, midAngle, slotR2, isWinner}) => {
        let txt = s.label.toUpperCase();
        let fontFamily = "'Rajdhani', 'Orbitron', Arial, sans-serif";
        let minFontSize = 11, maxFontSize = 22;
        const margin = 11;
        let minR = 65 - margin;
        let maxRR = slotR2 - 10 - margin;
        let availableHeight = maxRR - minR;
        let fontSize = maxFontSize;
        let letters = txt.split('');
        let letterSpace = 0.83;
        let fits = false;
        let textHeight = 0;

        while (fontSize >= minFontSize) {
            textHeight = (letters.length-1) * fontSize * letterSpace;
            if (textHeight <= availableHeight) { fits = true; break; }
            fontSize--;
        }
        if (!fits) fontSize = minFontSize;

        let startY = maxRR - (availableHeight - textHeight) / 2;
        letters.forEach((ch, i) => {
            let r = startY - i * fontSize * letterSpace;
            let angle = midAngle;
            let x = CX + r * Math.cos(angle);
            let y = CY + r * Math.sin(angle);
            let rotate = (angle * 180 / Math.PI) + 90;
            let textElem = document.createElementNS("http://www.w3.org/2000/svg", "text");
            textElem.setAttribute("x", x);
            textElem.setAttribute("y", y);
            textElem.setAttribute("font-family", fontFamily);
            textElem.setAttribute("font-size", fontSize);
            textElem.setAttribute("fill", isWinner ? (s.type === "preguntadeoro" ? "#ad8100" : s.textColor) : "#fff");
            textElem.setAttribute("font-weight", "bold");
            textElem.setAttribute("letter-spacing", "1px");
            textElem.setAttribute("text-anchor", "middle");
            textElem.setAttribute("dominant-baseline", "middle");
            textElem.setAttribute("style", `filter: drop-shadow(0 0 3px ${isWinner ? s.textColor : '#fff'});`);
            textElem.setAttribute("transform", `rotate(${rotate} ${x} ${y})`);
            textElem.textContent = ch;
            svg.appendChild(textElem);
        });
    });
}

function getSlotAtAngle(currentAngle) {
    let angle = (1.5 * Math.PI - (currentAngle % (2*Math.PI)) + 2*Math.PI) % (2*Math.PI);
    let a0 = 0;
    for (let idx = 0; idx < slots.length; idx++) {
        let ang = slots[idx].size * 2 * Math.PI;
        if (angle >= a0 && angle < a0 + ang) return idx;
        a0 += ang;
    }
    return 0;
}

// --- GIRO: inicia y se frena con segundo click ---
let currentSpinSpeed = 0;
let minSpeed = 0.011;
let maxSpeed = 0.29;
let decelStep = 0.989;

function startSpin() {
    if (spinning) return;
    spinning = true;
    stopRequested = false;
    currentSpinSpeed = maxSpeed * (0.87 + Math.random()*0.19);
    selectedSlotIdx = null;

    smoothFrenando = false;
    targetAngle = null;

    function spinLoop() {
        if (!spinning) return;
        currentAngle += currentSpinSpeed;
        if (currentAngle >= 2*Math.PI) currentAngle -= 2*Math.PI;
        drawRuleta(currentAngle, null, 0);

        if (stopRequested) {
            currentSpinSpeed *= decelStep;
            if (currentSpinSpeed <= minSpeed) {
                spinning = false;
                stopRequested = false;
                finalizeSpin();
                return;
            }
        }
        spinAnimation = requestAnimationFrame(spinLoop);
    }
    spinLoop();
}

let lastSpecialSlot = null;
let isSecondSpin = false; // ✅ NUEVO: Flag para detectar segundo giro

function finalizeSpin() {
    currentAngle = currentAngle % (2 * Math.PI);
    selectedSlotIdx = getSlotAtAngle(currentAngle);
    let selectedSlot = slots[selectedSlotIdx];
    let selectedCategory = selectedSlot?.label;
    let slotType = selectedSlot?.type || '';
    let isSpecial = slotType === 'soloyo' || slotType === 'respondeelchat' || slotType === 'preguntadeoro';

    let highlightFrames = 32;
    let f = 0;
    function highlightAnim() {
        let t = Math.min(f / (highlightFrames-1), 1);
        drawRuleta(currentAngle, selectedSlotIdx, t);
        f++;
        if(f < highlightFrames) {
            requestAnimationFrame(highlightAnim);
        } else {
            drawRuleta(currentAngle, selectedSlotIdx, 1);
            spinning = false;
            stopRequested = false;

            console.log('== Ruleta finalizó. Slot seleccionado:', selectedSlot);
            console.log('== Es segundo giro?', isSecondSpin);

            // ✅ VALIDACIÓN: Si es segundo giro y cayó en especial, girar de nuevo automáticamente
            if (isSecondSpin && isSpecial) {
                console.warn('⚠️ SEGUNDO GIRO: Cayó en especial de nuevo, relanzando...');
                setTimeout(() => {
                    startSpin();
                    setTimeout(() => {
                        stopRequested = true;
                    }, 2000); // Gira 2 segundos y para
                }, 500);
                return;
            }

            // LOGICA DE DOBLE GIRO
            if (lastSpecialSlot !== null) {
                // SEGUNDO GIRO: mandar pregunta usando el especial guardado
                let payload = { categoria: selectedCategory, special_slot: lastSpecialSlot };
                console.log('Enviando payload (doble giro):', payload);
                pendingSpecialBanner = payload.special_slot;
                fetch('/overlay/lanzar-pregunta', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });
                lastSpecialSlot = null;
                isSecondSpin = false; // ✅ Resetear flag
            } else if (isSpecial) {
                // PRIMER GIRO ESPECIAL: solo guardá el texto, no lances nada
                lastSpecialSlot = selectedCategory;
                isSecondSpin = true; // ✅ Activar flag para el próximo giro
                console.log('🎯 Categoría especial detectada, segundo giro activado');
            } else {
                // GIRO NORMAL: pregunta directa
                let payload = { categoria: selectedCategory };
                console.log('Enviando payload (giro normal):', payload);
                fetch('/overlay/lanzar-pregunta', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });
                lastSpecialSlot = null;
                isSecondSpin = false; // ✅ Asegurar que está en false
            }
        }
    }
    highlightAnim();
}


document.getElementById('spin-btn').onclick = function() {
    if (!spinning) {
        startSpin();
    } else if (!stopRequested) {
        stopRequested = true;
    }
};

drawRuleta(0);

window.addEventListener('DOMContentLoaded', fetchOverlayState);


window.girarRuletaRemoto = function() {
    // Esto simula el click en el botón o inicia el giro:
    if (!spinning) {
        startSpin();
    } else if (!stopRequested) {
        stopRequested = true;
    }
};
    </script>
</body>
</html>

