<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use App\Models\HomeMatch;

class HomeMatchHoje implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private $matchHomeHoje;

    public function __construct(HomeMatch $matchHomeHoje)
    {
        $this->matchHomeHoje = $matchHomeHoje;
    }

    public function broadcastOn()
    {
        return new Channel('home-match-hoje');
    }

    public function broadcastWith()
    {
        return $this->matchHomeHoje->toArray();
    }
}
