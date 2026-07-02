<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MatchModel;
use App\Models\Configuracao;
use App\Models\BlockLeague;
use App\Models\BlockMatchModel;
use App\Models\ConfigMercados;
use App\Models\ConfigOdd;
use App\Models\BlockOddMatchModel;
use App\Events\LoadMatchLiveScore;
use App\Jobs\LoadMatchLive;

class LiveScoreMultiSport extends Command
{
    protected $signature = 'apifootball:live-multi {--sport= : Filtrar por esporte (basketball, volleyball)}';
    protected $description = 'Atualiza placares ao vivo para todos os esportes (futebol, basquete, volei)';

    public function handle()
    {
        $siteId = config('tenant.site_id', 1);
        $sportFilter = $this->option('sport');

        $config = Configuracao::where('site_id', $siteId)->first();
        $timeLive = $config->time_live ?? 90;

        $leageBlock = BlockLeague::where('site_id', $siteId)->pluck('league')->toArray();
        $blockMatch = BlockMatchModel::where('site_id', $siteId)->pluck('event_id')->toArray();

        $query = MatchModel::where('time_status', 1)
            ->where('time', '<=', $timeLive)
            ->whereNotIn('league', $leageBlock)
            ->whereNotIn('event_id', $blockMatch)
            ->orderBy('league', 'asc');

        if ($sportFilter) {
            $sportMap = [
                'basketball' => 2,
                'volleyball' => 3,
                'football'   => 1,
            ];
            if (isset($sportMap[$sportFilter])) {
                $query->where('sport_id', $sportMap[$sportFilter]);
            }
        }

        $matches = $query->get();

        $count = 0;
        foreach ($matches as $match) {
            $event = MatchModel::find($match->id);
            broadcast(new LoadMatchLiveScore($event));
            LoadMatchLive::dispatchNow($event, $match->id);
            $count++;
        }

        $this->info("Live scores atualizados: {$count} partidas" .
            ($sportFilter ? " (esporte: {$sportFilter})" : " (todos)"));

        return 0;
    }
}
