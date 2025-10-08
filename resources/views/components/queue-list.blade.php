{{-- resources/views/components/queue-list.blade.php --}}
<div class="neon-queue-box" id="queue-container">
    <div class="queue-title">
        Participantes
    </div>
    @if(count($participants))
        <ol class="neon-queue-list">
            @foreach($participants->sortByDesc('puntaje') as $p)
                <li data-participant-id="{{ $p->id }}">
                    <span class="neon-queue-num">#{{ $loop->iteration }}</span>
                    <span class="neon-queue-username">{{ $p->username }}</span>
                    <span class="neon-queue-dni">({{ $p->dni_last4 }})</span>
                    @if(isset($p->puntaje))
                        <span class="neon-queue-score" data-score="{{ $p->puntaje }}">
                            {{ $p->puntaje }} pts
                        </span>
                    @endif
                    @if($p->status === 'active' || $p->status === 'playing')
                        <span class="neon-queue-playing">(jugando)</span>
                    @endif
                </li>
            @endforeach
        </ol>
    @else
        <div class="neon-queue-empty">Sin participantes</div>
    @endif
</div>

<style>
.neon-queue-box {
    background: #111b2b;
    border-radius: 13px;
    border: 1.3px solid #00f0ff44;
    padding: 20px 25px 14px 25px;
    margin-bottom: 22px;
    box-shadow: 0 0 13px #19faff32;
    max-width: 370px;
    width: 100%;
    box-sizing: border-box;
}
.queue-title {
    color: #00f0ff;
    text-shadow: 0 0 7px #00f0ffbb;
    font-size: 1.2rem;
    font-weight: bold;
    text-align: center;
    margin-bottom: 13px;
    letter-spacing: 1px;
}
.neon-queue-list {
    padding-left: 6px;
    margin-bottom: 0;
}
.neon-queue-list li {
    margin-bottom: 7px;
    font-weight: 500;
    font-size: 1.06em;
    color: #ffe47a;
    display: flex;
    align-items: center;
    border-bottom: 1px dashed #19faff22;
    padding-bottom: 2px;
    gap: 5px;
    transition: transform 0.3s ease;
}
.neon-queue-list li.pulse {
    animation: pulse-green 0.6s ease;
}
@keyframes pulse-green {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); background: rgba(25, 255, 140, 0.1); }
}
.neon-queue-num {
    color: #00f0ff;
    font-size: 1.01em;
    font-weight: bold;
    margin-right: 8px;
}
.neon-queue-username {
    color: #ffe47a;
    font-weight: bold;
    margin-right: 9px;
}
.neon-queue-dni {
    color: #aee;
    font-size: 0.94em;
    margin-right: 5px;
}
.neon-queue-score {
    color: #19ff8c;
    font-weight: 700;
    font-size: 1em;
    margin-left: 6px;
    margin-right: 0;
    text-shadow: 0 0 7px #19ff8c, 0 0 2px #fff3;
    letter-spacing: 0.3px;
    background: none !important;
    border-radius: 0 !important;
    padding: 0 !important;
    display: inline;
}
.neon-queue-playing {
    color: #19ff8c;
    font-weight: bold;
    font-size: 0.97em;
    margin-left: 8px;
    text-shadow: 0 0 8px #1affd2a5;
}
.neon-queue-empty {
    color: #ccc;
    text-align: center;
    font-size: 1.07rem;
    margin: 12px 0 7px 0;
}
@media (max-width: 700px) {
    .neon-queue-box {
        width: 100% !important;
        max-width: 100% !important;
        min-width: 0 !important;
        padding: 13px 4px 10px 4px !important;
        margin: 0 0 22px 0 !important;
        box-sizing: border-box !important;
        overflow-x: auto !important;
    }
}
</style>

<script>
// Solo ejecutar si Echo está disponible
if (window.Echo) {
    // Escuchar evento de actualización de puntajes de participantes
    window.Echo.channel('overlay-channel')
        .listen('.revelar-respuesta', (e) => {
            console.log('[QUEUE] Evento revelar recibido:', e);
            
            const puntajes = e.puntajes_participantes || e.data?.puntajes_participantes || {};
            
            // Actualizar cada participante en la lista
            Object.keys(puntajes).forEach(participantId => {
                const nuevosPuntos = puntajes[participantId];
                const li = document.querySelector(`li[data-participant-id="${participantId}"]`);
                
                if (li) {
                    const scoreSpan = li.querySelector('.neon-queue-score');
                    if (scoreSpan) {
                        const puntosAnteriores = parseInt(scoreSpan.dataset.score) || 0;
                        
                        // Solo actualizar si cambió
                        if (nuevosPuntos !== puntosAnteriores) {
                            scoreSpan.textContent = nuevosPuntos + ' pts';
                            scoreSpan.dataset.score = nuevosPuntos;
                            
                            // Animación de pulso
                            li.classList.add('pulse');
                            setTimeout(() => li.classList.remove('pulse'), 600);
                        }
                    }
                }
            });
            
            // Reordenar lista por puntaje (opcional)
            reordenarLista();
        });
}

function reordenarLista() {
    const lista = document.querySelector('.neon-queue-list');
    if (!lista) return;
    
    const items = Array.from(lista.querySelectorAll('li'));
    
    // Ordenar por puntaje descendente
    items.sort((a, b) => {
        const scoreA = parseInt(a.querySelector('.neon-queue-score')?.dataset.score || 0);
        const scoreB = parseInt(b.querySelector('.neon-queue-score')?.dataset.score || 0);
        return scoreB - scoreA;
    });
    
    // Reordenar en el DOM
    items.forEach((item, index) => {
        const numSpan = item.querySelector('.neon-queue-num');
        if (numSpan) numSpan.textContent = '#' + (index + 1);
        lista.appendChild(item);
    });
}
</script>