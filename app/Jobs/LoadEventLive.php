<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Carbon\Carbon;
use App\Models\MatchModel;
use App\Models\Odd;
use App\Models\Configuracao;
use App\Models\BlockMatch;
use App\Models\BlockOddMatch;
use Illuminate\Support\Facades\Cache;

class LoadEventLive implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $siteId;

    public function __construct($siteId = null)
    {
        $this->siteId = $siteId ?? config('tenant.site_id', 1);
    }

    public function liveFutebol()
    {
        $siteId = $this->siteId;

        $config       = Configuracao::where('site_id', $siteId)->first();
        $cotacaoLive  = $config->cotacao_live ?? 0;
        $timeLive     = $config->time_live ?? 0;
        $oddZ         = $config->bloquear_odd_abaixo ?? 1.01;

        $blockMatchIds = BlockMatch::where('site_id', $siteId)->pluck('event_id')->toArray();
        $blockedOdds   = BlockOddMatch::where('site_id', $siteId)->where('status', 0)->pluck('odd_uid')->toArray();
        $alteredOdds   = BlockOddMatch::where('site_id', $siteId)->where('status', 1)->get()->pluck('cotacao', 'odd_id')->toArray();

        $leagues = MatchModel::select('league')
            ->where('site_id', $siteId)
            ->where('time_status', 1)
            ->where('time', '<=', $timeLive)
            ->whereNotIn('event_id', $blockMatchIds)
            ->groupBy('league')
            ->orderBy('league', 'asc')
            ->get();

        $return = [];

        foreach ($leagues as $league) {
            $matchs = MatchModel::where('site_id', $siteId)
                ->where('league', $league->league)
                ->where('time_status', 1)
                ->where('time', '<=', $timeLive)
                ->with(['fullOdds' => fn($q) => $q->orderBy('market_name')->orderBy('order')])
                ->get();

            $i = count($return);
            $return[$i]['league'] = $league->league;
            $j = 0;

            foreach ($matchs as $match) {
                $odds = $match->fullOdds;

                $return[$i]['match'][$j] = [
                    'id'                     => $match->id,
                    'event_id'               => $match->event_id,
                    'sport'                  => $match->sport_name,
                    'confronto'              => $match->confronto,
                    'home'                   => $match->home,
                    'image_id_home'          => $match->image_id_home,
                    'away'                   => $match->away,
                    'image_id_away'          => $match->image_id_away,
                    'score'                  => $match->score,
                    'date'                   => $match->date,
                    'time'                   => $match->time,
                    'halfTimeScoreHome'      => $match->halfTimeScoreHome,
                    'halfTimeScoreAway'      => $match->halfTimeScoreAway,
                    'fullTimeScoreHome'      => $match->fullTimeScoreHome,
                    'fullTimeScoreAway'      => $match->fullTimeScoreAway,
                    'numberOfCornersHome'    => $match->numberOfCornersHome,
                    'numberOfCornersAway'    => $match->numberOfCornersAway,
                    'numberOfYellowCardsHome'=> $match->numberOfYellowCardsHome,
                    'numberOfYellowCardsAway'=> $match->numberOfYellowCardsAway,
                    'numberOfRedCardsHome'   => $match->numberOfRedCardsHome,
                    'numberOfRedCardsAway'   => $match->numberOfRedCardsAway,
                    'count_odd'              => $odds->count(),
                ];

                $q = 0;
                foreach ($odds as $odd) {
                    $cotacaoOriginal = $odd->value;
                    $redu    = $cotacaoOriginal - 1;
                    $oddFinal = ($redu * $cotacaoLive / 100) + $redu + 1;

                    if (in_array($odd->id, $blockedOdds)) {
                        $cotacao = 0;
                    } elseif (isset($alteredOdds[$odd->id])) {
                        $cotacao = round($alteredOdds[$odd->id], 2);
                    } elseif ($oddFinal <= $oddZ) {
                        $cotacao = 0;
                    } else {
                        $cotacao = round($oddFinal, 2);
                    }

                    $return[$i]['match'][$j]['odds'][$q] = [
                        'id'               => $odd->id,
                        'group_opp'        => $odd->market_name,
                        'odd'              => $odd->label,
                        'cotacaoOriginal'  => $cotacaoOriginal,
                        'cotacao'          => $cotacao,
                        'type'             => $odd->type,
                    ];
                    $q++;
                }

                if (empty($return[$i]['match'][$j]['odds'])) {
                    for ($q = 0; $q < 3; $q++) {
                        $return[$i]['match'][$j]['odds'][$q] = [
                            'id' => $match->event_id . $match->id . $q,
                            'group_opp' => 'Vencedor do Encontro',
                            'odd' => $q, 'cotacao' => 0, 'type' => 'live',
                        ];
                    }
                }

                $j++;
            }
        }

        return $return;
    }

    public function handle()
    {
        $data = $this->liveFutebol();
        Cache::put("live_futebol_{$this->siteId}", $data, now()->addMinutes(1));
        return $data;
    }
}
