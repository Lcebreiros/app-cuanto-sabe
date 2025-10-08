<?php

// app/Events/ParticipantQueueUpdated.php
namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ParticipantQueueUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $sessionId;
    public $timestamp;

    public function __construct($sessionId)
    {
        $this->sessionId = $sessionId;
        $this->timestamp = now()->toISOString();
        
        \Log::info("ParticipantQueueUpdated event created for session: {$sessionId}");
    }

    public function broadcastOn()
    {
        $channelName = "queue-session-{$this->sessionId}";
        \Log::info("Broadcasting ParticipantQueueUpdated on channel: {$channelName}");
        
        return new Channel($channelName);
    }

    public function broadcastAs()
    {
        return 'ParticipantQueueUpdated';
    }

    public function broadcastWith()
    {
        return [
            'session_id' => $this->sessionId,
            'timestamp' => $this->timestamp,
            'message' => 'Participant queue updated'
        ];
    }
}