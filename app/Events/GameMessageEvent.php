<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class GameMessageEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    private $lobbyId;
    /**
     * Create a new event instance.
     *
     * @return void>>>
     */
    public function __construct($lobbyId, $message)
    {
        $this->message = $message;
        $this->lobbyId = $lobbyId;
    }

    public function broadcastOn()
    {
        return new PresenceChannel('game.' . $this->lobbyId);
    }
}
