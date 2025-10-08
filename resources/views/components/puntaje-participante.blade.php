@props(['puntaje'])

@php
    // Aseguramos que $totalPuntaje siempre tenga un número
    $totalPuntaje = 0;

    if (is_array($puntaje) && isset($puntaje['total'])) {
        $totalPuntaje = $puntaje['total'];
    } elseif (is_numeric($puntaje)) {
        $totalPuntaje = $puntaje;
    }
@endphp

<div id="puntaje-container" style="
    background: linear-gradient(90deg, #001a35ee 0%, #072954ea 100%);
    border: 4px solid #01e3fd66;
    border-radius: 1.5rem;
    box-shadow: 0 4px 32px #020d2455;
    padding: 0.8rem 1.3rem 0.8rem 1.1rem;
    min-width: 120px;
    max-width: 340px;
    width: fit-content;
    display: flex;
    align-items: center;
    justify-content: flex-start;
    gap: 1.1rem;
    text-align: left;
">
    <span style="
        color: #00f0ff;
        font-size: 1.28rem;
        font-weight: bold;
        text-shadow: 0 0 10px #00e8fc, 0 0 4px #fff2;
        letter-spacing: 0.02em;
        white-space: nowrap;
        flex-shrink: 0;
    ">
        Tus puntos:
    </span>
    <span id="puntaje-num" style="
        color: #19ff8c;
        font-size: 2.25rem;
        font-weight: 900;
        line-height: 1;
        text-shadow: 0 0 12px #19ff8caa, 0 0 3px #fff3;
        margin-left: 0.2em;
        letter-spacing: 0.01em;
        white-space: nowrap;
    ">
        {{ $totalPuntaje }}
    </span>
</div>

{{-- NO HAY SCRIPT AQUÍ - Todo se maneja desde participar.blade.php --}}