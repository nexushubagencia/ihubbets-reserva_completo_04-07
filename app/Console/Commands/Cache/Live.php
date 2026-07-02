<?php

namespace App\Console\Commands\Cache;

use Illuminate\Console\Command;
use App\Models\MatchModel;
use App\Models\Odd;
use App\Models\Configuracao;
use App\Models\BlockMatch;
use App\Models\BlockOddMatch;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class Live extends Command
{
    protected $signature = 'command:live';
    protected $description = 'Monta cache dos jogos ao vivo de futebol com comissão live aplicada';

    public function handle()
    {
        $siteId = config('tenant.site_id', 1);

        try {
            $this->info("Iniciando Live (site_id: {$siteId})...");

            $config      = Configuracao::where('site_id', $siteId)->first();
            $cotacaoLive = $config->cotacao_live ?? 0;
            $timeLive    = $config->time_live ?? 0;
            $oddZ        = $config->bloquear_odd_abaixo ?? 1.01;

            $blockMatchIds = BlockMatch::where('site_id', $siteId)
                ->pluck('event_id')
                ->toArray();

            $blockedOdds = BlockOddMatch::where('site_id', $siteId)
                ->where('status', 0)
                ->pluck('odd_uid')
                ->toArray();

            $alteredOdds = BlockOddMatch::where('site_id', $siteId)
                ->where('status', 1)
                ->get()
                ->pluck('cotacao', 'odd_id')
                ->toArray();

            $leagues = MatchModel::select('league')
                ->where('site_id', $siteId)
                ->where('sport_id', 1)
                ->where('time_status', 1)
                ->where('time', '<=', $timeLive)
                ->whereNotIn('event_id', $blockMatchIds)
                ->groupBy('league')
                ->orderBy('league', 'asc')
                ->get();

            $return = [];

            foreach ($leagues as $league) {
                $matchs = MatchModel::where('site_id', $siteId)
                    ->where('sport_id', 1)
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
                        'id'                         => $match->id,
                        'event_id'                   => $match->event_id,
                        'sport'                      => $match->sport_name,
                        'confronto'                  => $match->confronto,
                        'home'                       => $match->home,
                        'image_id_home'              => $match->image_id_home,
                        'away'                       => $match->away,
                        'image_id_away'              => $match->image_id_away,
                        'score'                      => $match->score,
                        'date'                       => $match->date,
                        'time'                       => $match->time,
                        'halfTimeScoreHome'          => $match->halfTimeScoreHome ?? null,
                        'halfTimeScoreAway'          => $match->halfTimeScoreAway ?? null,
                        'fullTimeScoreHome'          => $match->fullTimeScoreHome ?? null,
                        'fullTimeScoreAway'          => $match->fullTimeScoreAway ?? null,
                        'numberOfCornersHome'        => $match->numberOfCornersHome ?? null,
                        'numberOfCornersAway'        => $match->numberOfCornersAway ?? null,
                        'numberOfYellowCardsHome'    => $match->numberOfYellowCardsHome ?? null,
                        'numberOfYellowCardsAway'    => $match->numberOfYellowCardsAway ?? null,
                        'numberOfRedCardsHome'       => $match->numberOfRedCardsHome ?? null,
                        'numberOfRedCardsAway'       => $match->numberOfRedCardsAway ?? null,
                        'count_odd'                  => $odds->count(),
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
                                'id'         => $match->event_id . $match->id . $q,
                                'group_opp'  => 'Vencedor do Encontro',
                                'odd'        => $q,
                                'cotacao'    => 0,
                                'type'       => 'live',
                            ];
                        }
                    }

                    $j++;
                }
            }

            $cacheKey = "live_data_{$siteId}";
            Cache::put($cacheKey, $return, now()->addMinutes(1));

            $totalMatches = collect($return)->sum(fn($l) => count($l['match'] ?? []));
            $this->info("Live concluído: " . count($return) . " ligas, {$totalMatches} jogos. Cache: {$cacheKey}");

            return $return;

        } catch (\Exception $e) {
            Log::error("Erro no Live: " . $e->getMessage());
            $this->error("Erro: " . $e->getMessage());
            return [];
        }
    }
}
