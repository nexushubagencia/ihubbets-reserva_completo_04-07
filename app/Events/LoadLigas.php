<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class LoadLigas implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private $ligas;

    public function __construct($ligas)
    {
        $this->ligas = $ligas;
    }

    public function broadcastOn()
    {
        return new Channel('load-league');
    }

    public function broadcastWith()
    {
        return $this->ligas;
    }
}
