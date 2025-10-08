<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class GuestPointsUpdated implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public $sessionId;
    public $points;

    public function __construct($sessionId, $points)
    {
        $this->sessionId = $sessionId;
        $this->points = $points;
    }

public function broadcastOn()
{
    $channels = [];
    if ($this->sessionId) $channels[] = new Channel('overlay-session-' . $this->sessionId);
    $channels[] = new Channel('overlay-channel'); // <- SIEMPRE
    return $channels;
}


    public function broadcastAs()
    {
        return 'GuestPointsUpdated';
    }
}
