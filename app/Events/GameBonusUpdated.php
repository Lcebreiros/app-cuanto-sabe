<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\GameSession;

class GameBonusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $apuesta_x2_active;
    public $descarte_usados;

public $session;

public function __construct(GameSession $session)
{
    $this->session = $session;
}


    public function broadcastOn()
    {
        return new Channel('overlay-channel');
    }

    // ✅ IMPORTANTE: Define el nombre del evento
    public function broadcastAs()
    {
        return 'GameBonusUpdated';
    }

    public function broadcastWith()
{
    return [
        'apuesta_x2_active' => $this->session->apuesta_x2_active,
        'apuesta_x2_usadas' => $this->session->apuesta_x2_usadas,
        'descarte_usados' => $this->session->descarte_usados,
        'modo_juego' => $this->session->modo_juego,
    ];
}
}