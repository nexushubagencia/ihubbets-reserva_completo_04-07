<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class RealTimeHome implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private $live;

    public function __construct($live)
    {
        $this->live = $live;
    }

    public function broadcastOn()
    {
        return new Channel('real-time-home');
    }

    public function broadcastWith()
    {
        return $this->live;
    }
}
