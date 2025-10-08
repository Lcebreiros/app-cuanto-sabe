<?php
/*
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Ruleta Neon Elegante - Sobria Azul</title>
  <meta name="viewport" content="width=440">
  <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@700&family=Rajdhani:wght@700&display=swap" rel="stylesheet">
  <style>
    html, body {
      margin: 0; padding: 0; background: transparent;
      width: 100vw; height: 100vh; overflow: hidden;
      display: flex; justify-content: center; align-items: flex-end;
    }
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
  </style>
</head>
<body>
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
<script>
  window.sessionGame = @json($sessionGame);

  // Colores armónicos:
  const colorBase = "#153364";      // azul slot base
  const borderNeon = "#2d60a6";    // azul vibrante bordes
  const borderShadow = "#193a68";  // sombra azul exterior
  const slotShadow = "#070c16f6";  // sombra más oscura entre slots
  const winnerColor = "#2987d8";   // slot destacado
  const innerDarkStroke = "#111927"; // borde oscuro entre slots

  const categories = (window.sessionGame && window.sessionGame.categories) ? window.sessionGame.categories : [];
  const fixedTypes = ["pregunta de oro", "responde el chat", "solo yo", "random"];
  const sizePresets = {
    "pregunta de oro": 0.05,
    "responde el chat": 0.12,
    "solo yo": 0.12,
    "random": 0.20
  };
const neonGreen = "#22fa68"; // Verde neón elegante
const neonGreenText = "#eaffdb"; // Texto claro para fondo verde

const slots = categories.map(cat => ({
  label: cat.label,
  color: cat.fixed
    ? (cat.color || winnerColor) // Fijos siguen con azul
    : neonGreen,                // Los NO fijos usan verde neón
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

  // SVG Consts
  const svg = document.getElementById('ruleta-svg');
  const W = 440, H = 440, CX = W / 2, CY = H / 2, R = 205, R2 = 189;
  let currentAngle = 0;
  let selectedSlotIdx = null;

  // ---- Giro en dos fases
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

    // Marcos entre slots (azul + borde oscuro)
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

      // Borde principal (azul vibrante)
      let borderPath = document.createElementNS("http://www.w3.org/2000/svg", "path");
      borderPath.setAttribute("d", pathData);
      borderPath.setAttribute("fill", "none");
      borderPath.setAttribute("stroke", borderNeon);
      borderPath.setAttribute("stroke-width", "2.2");
      borderPath.setAttribute("filter", "url(#slotGlow)");
      svg.appendChild(borderPath);

      // Borde oscuro interno
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
  let decelStep = 0.989; // menor = más lento el frenado

  function startSpin() {
    if (spinning) return;
    spinning = true;
    stopRequested = false;
    currentSpinSpeed = maxSpeed * (0.87 + Math.random()*0.19);
    selectedSlotIdx = null;

    function spinLoop() {
      if (!spinning) return;
      currentAngle += currentSpinSpeed;
      if (currentAngle >= 2*Math.PI) currentAngle -= 2*Math.PI;
      drawRuleta(currentAngle, null, 0);

      // Si pidieron frenar
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

  function finalizeSpin() {
    currentAngle = currentAngle % (2 * Math.PI);
    selectedSlotIdx = getSlotAtAngle(currentAngle);
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
</script>
</body>
</html>
*/
