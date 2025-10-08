<?php

// App\Events\OpcionSeleccionada.php
namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\InteractsWithSockets;
// CAMBIÁ ESTA LINEA:
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class OpcionSeleccionada implements ShouldBroadcastNow // <-- CAMBIÁ ESTO
{
    use InteractsWithSockets, SerializesModels;

    public $opcion;

    public function __construct($opcion)
    {
        $this->opcion = $opcion;
    }

    public function broadcastOn()
    {
        return new Channel('overlay-channel');
    }

    public function broadcastAs()
    {
        return 'opcion-seleccionada';
    }
}
