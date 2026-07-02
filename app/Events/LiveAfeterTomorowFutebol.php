<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class LiveAfeterTomorowFutebol implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private $valor;

    public function __construct($valor)
    {
      $this->valor = $valor;
    }

    public function broadcastOn()
    {
        return new Channel('live-futebol-after');
    }

    public function broadcastWith()
    {
        return $this->valor;
    }
}
