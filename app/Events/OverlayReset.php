<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OverlayReset implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public function broadcastOn()
    {
        return new Channel('overlay-channel');
    }

    public function broadcastAs()
    {
        return 'overlay-reset';
    }
}
