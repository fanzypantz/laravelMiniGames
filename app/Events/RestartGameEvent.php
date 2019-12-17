<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class RestartGameEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private $lobbyId;
    /**
     * Create a new event instance.
     *
     * @return void>>>
     */
    public function __construct($lobbyId)
    {
        $this->lobbyId = $lobbyId;
    }

    public function broadcastOn()
    {
        return new PresenceChannel('game.' . $this->lobbyId);
    }
}
