<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use App\Models\MatchModel;

class LoadMatchLiveScore implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private $match;

    public function __construct(MatchModel $match)
    {
        $this->match = $match;
    }

    public function broadcastOn()
    {
        return new Channel('match-load');
    }

    public function broadcastWith()
    {
        return $this->match->toArray();
    }
}
