<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class PuntajeActualizado implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public $participantSessionId;
    public $puntaje;

    public function __construct($participantSessionId, $puntaje)
    {
        $this->participantSessionId = $participantSessionId;
        $this->puntaje = $puntaje; // Total, o array completo según prefieras
    }

    public function broadcastOn()
    {
        return new Channel('puntaje.' . $this->participantSessionId);
    }

    public function broadcastAs()
{
    return 'PuntajeActualizado';
}

}
