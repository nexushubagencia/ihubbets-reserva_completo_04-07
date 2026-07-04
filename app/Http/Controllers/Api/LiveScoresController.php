<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class LiveScoresController extends Controller
{
    /**
     * Retorna placares ao vivo (leve, para polling)
     */
    public function liveScores()
    {
        $siteId = config('tenant.site_id', 1);
        $cacheKey = "live_scores_{$siteId}";

        $data = Cache::remember($cacheKey, 15, function() use ($siteId) {
            return DB::table('matchs')
                ->where('site_id', $siteId)
                ->where('sport_id', 1)
                ->where('live_status', 1)
                ->select('id', 'event_id', 'home', 'away', 'score', 'time', 'date')
                ->get()
                ->toArray();
        });

        return response()->json($data);
    }

    /**
     * Retorna partidas da home (cache 5min)
     */
    public function homeMatches()
    {
        $siteId = config('tenant.site_id', 1);
        $sportId = 1;
        $cacheKey = "home_matches_{$siteId}_{$sportId}";

        $data = Cache::get($cacheKey, []);

        return response()->json($data);
    }
}
