<?php

namespace App\Console\Commands\Cache;

use Illuminate\Console\Command;
use App\Models\MatchModel;
use App\Models\Odd;
use App\Models\Configuracao;
use App\Models\BlockMatch;
use App\Models\BlockOddMatch;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AtualizaHome extends Command
{
    protected $signature = 'command:atualizaHome {sport_id=1}';
    protected $description = 'Monta cache dos jogos de hoje agrupados por liga com odds para a Home';

    public function handle()
    {
        $sportId = (int) $this->argument('sport_id');
        $siteId  = config('tenant.site_id', 1);

        try {
            $this->info("Iniciando AtualizaHome (site_id: {$siteId}, sport_id: {$sportId})...");

            $config = Configuracao::where('site_id', $siteId)->first();
            $oddZ   = $config->bloquear_odd_abaixo ?? 1.01;
            $oddM   = $config->travar_odd_acima ?? 50.0;

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

            $agora     = Carbon::now();
            $dataHoje  = Carbon::today()->format('Y-m-d');
            $fimDoDia  = $dataHoje . ' 23:59:00';

            $leagues = MatchModel::select('league')
                ->where('site_id', $siteId)
                ->where('sport_id', $sportId)
                ->where('date', '>=', $agora)
                ->where('date', '<=', $fimDoDia)
                ->where('visible', 'Sim')
                ->whereNotIn('event_id', $blockMatchIds)
                ->groupBy('league')
                ->orderBy('league', 'asc')
                ->get();

            $return = [];

            foreach ($leagues as $league) {
                $query = MatchModel::where('site_id', $siteId)
                    ->where('sport_id', $sportId)
                    ->where('league', $league->league)
                    ->where('date', '>=', $agora)
                    ->where('date', '<=', $fimDoDia)
                    ->where('visible', 'Sim')
                    ->with(['fullOdds' => fn($q) => $q->orderBy('market_name')->orderBy('order')]);

                $matchs = $query->get();

                $i = count($return);
                $return[$i]['league'] = $league->league;
                $j = 0;

                foreach ($matchs as $match) {
                    $odds = $match->fullOdds;

                    if ($sportId != 1 && $odds->isEmpty()) {
                        continue;
                    }

                    $return[$i]['match'][$j] = [
                        'id'            => $match->id,
                        'event_id'      => $match->event_id,
                        'sport'         => $match->sport_name,
                        'confronto'     => $match->confronto,
                        'home'          => $match->home,
                        'image_id_home' => $match->image_id_home,
                        'away'          => $match->away,
                        'image_id_away' => $match->image_id_away,
                        'date'          => $match->date,
                        'count_odd'     => $odds->count(),
                    ];

                    $q = 0;
                    foreach ($odds as $odd) {
                        $cotacao = $odd->value;

                        if (in_array($odd->id, $blockedOdds)) {
                            $cotacao = 0;
                        } elseif (isset($alteredOdds[$odd->id])) {
                            $cotacao = round($alteredOdds[$odd->id], 2);
                        } else {
                            if ($cotacao <= $oddZ) {
                                $cotacao = 0;
                            } elseif ($cotacao > $oddM) {
                                $cotacao = round($oddM, 2);
                            } else {
                                $cotacao = round($cotacao, 2);
                            }
                        }

                        $return[$i]['match'][$j]['odds'][$q] = [
                            'id'         => $odd->id,
                            'group_opp'  => $odd->market_name,
                            'odd'        => $odd->label,
                            'value'      => $odd->value,
                            'cotacao'    => $cotacao,
                            'type'       => $odd->type,
                        ];
                        $q++;
                    }

                    if (empty($return[$i]['match'][$j]['odds'])) {
                        for ($q = 0; $q < 3; $q++) {
                            $return[$i]['match'][$j]['odds'][$q] = [
                                'id'         => $match->event_id . $match->id . $q,
                                'group_opp'  => 'Vencedor do Encontro',
                                'odd'        => $q,
                                'value'      => 0,
                                'cotacao'    => 0,
                                'type'       => 'pre',
                            ];
                        }
                    }

                    $j++;
                }
            }

            $cacheKey = "home_matches_{$siteId}_{$sportId}";
            Cache::put($cacheKey, $return, now()->addMinutes(5));

            $totalMatches = collect($return)->sum(fn($l) => count($l['match'] ?? []));
            $this->info("AtalizaHome concluído: " . count($return) . " ligas, {$totalMatches} jogos. Cache: {$cacheKey}");

            return $return;

        } catch (\Exception $e) {
            Log::error("Erro no AtualizaHome (sport_id={$sportId}): " . $e->getMessage());
            $this->error("Erro: " . $e->getMessage());
            return [];
        }
    }
}
