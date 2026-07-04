<?php

namespace App\Console\Commands\Cache;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class UpdateLiveScores extends Command
{
    protected $signature = 'command:update-live-scores';
    protected $description = 'Atualiza cache de placares ao vivo (leve, para polling)';

    public function handle()
    {
        $siteId = config('tenant.site_id', 1);
        $cacheKey = "live_scores_{$siteId}";

        $data = DB::table('matchs')
            ->where('site_id', $siteId)
            ->where('sport_id', 1)
            ->where('live_status', 1)
            ->select('id', 'event_id', 'home', 'away', 'score', 'time', 'date')
            ->get()
            ->toArray();

        Cache::put($cacheKey, $data, now()->addSeconds(15));

        $this->info("Live scores atualizado: " . count($data) . " jogos ao vivo");
    }
}
