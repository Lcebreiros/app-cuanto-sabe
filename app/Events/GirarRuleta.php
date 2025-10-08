<?php
// app/Events/GirarRuleta.php
namespace App\Events;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class GirarRuleta implements ShouldBroadcastNow
{
    public function broadcastOn() { return new Channel('overlay-channel'); }
    public function broadcastAs() { return 'girar-ruleta'; }
}
