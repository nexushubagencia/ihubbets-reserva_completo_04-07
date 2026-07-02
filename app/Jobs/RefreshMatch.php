<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\MatchModel;
use Illuminate\Support\Facades\Cache;

class RefreshMatch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $matchId;
    private $siteId;

    public function __construct($matchId, $siteId = null)
    {
        $this->matchId = $matchId;
        $this->siteId  = $siteId ?? config('tenant.site_id', 1);
    }

    public function handle()
    {
        $match = MatchModel::where('id', $this->matchId)
            ->where('site_id', $this->siteId)
            ->with(['fullOdds'])
            ->first();

        if (!$match) {
            return null;
        }

        $data = [
            'id'            => $match->id,
            'event_id'      => $match->event_id,
            'sport'         => $match->sport_name,
            'confronto'     => $match->confronto,
            'home'          => $match->home,
            'away'          => $match->away,
            'image_id_home' => $match->image_id_home,
            'image_id_away' => $match->image_id_away,
            'score'         => $match->score,
            'time_status'   => $match->time_status,
            'date'          => $match->date,
            'odds'          => $match->fullOdds->map(fn($o) => [
                'id'        => $o->id,
                'market'    => $o->market_name,
                'label'     => $o->label,
                'value'     => $o->value,
                'type'      => $o->type,
            ]),
        ];

        Cache::put("match_{$this->matchId}", $data, now()->addMinutes(5));

        return $data;
    }
}
