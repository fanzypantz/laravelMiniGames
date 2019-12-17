<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class NewMessageEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;


    public $name;
    public $message;
    private $lobbyId;
    /**
     * Create a new event instance.
     *
     * @return void>>>
     */
    public function __construct($name, $message, $lobbyId)
    {
        $this->name = $name;
        $this->message = $message;
        $this->lobbyId = $lobbyId;
    }

    public function broadcastOn()
    {
        return new PresenceChannel('lobby.' . $this->lobbyId);
    }
}
