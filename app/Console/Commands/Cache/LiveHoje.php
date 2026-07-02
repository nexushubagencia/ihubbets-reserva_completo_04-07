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

class LiveHoje extends Command
{
    protected $signature = 'command:liveHoje';
    protected $description = 'Monta cache dos jogos ao vivo de hoje para todos os esportes com comissão aplicada';

    private array $sportIds = [1, 18, 91, 9, 151];

    public function handle()
    {
        $siteId = config('tenant.site_id', 1);

        try {
            $this->info("Iniciando LiveHoje (site_id: {$siteId})...");

            $config      = Configuracao::where('site_id', $siteId)->first();
            $oddZ        = $config->bloquear_odd_abaixo ?? 1.01;
            $oddM        = $config->travar_odd_acima ?? 50.0;
            $cotacaoLive = $config->cotacao_live ?? 0;

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

            $agora    = Carbon::now();
            $dataHoje = Carbon::today()->format('Y-m-d');
            $fimDoDia = $dataHoje . ' 23:59:00';

            $return = [];

            foreach ($this->sportIds as $sportId) {
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

                foreach ($leagues as $league) {
                    $matchs = MatchModel::where('site_id', $siteId)
                        ->where('sport_id', $sportId)
                        ->where('league', $league->league)
                        ->where('date', '>=', $agora)
                        ->where('date', '<=', $fimDoDia)
                        ->where('visible', 'Sim')
                        ->with(['fullOdds' => fn($q) => $q->orderBy('market_name')->orderBy('order')])
                        ->get();

                    $i = count($return);
                    $return[$i]['league']  = $league->league;
                    $return[$i]['sport_id'] = $sportId;
                    $j = 0;

                    foreach ($matchs as $match) {
                        $odds = $match->fullOdds;

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
                            $cotacaoOriginal = $odd->value;
                            $redu    = $cotacaoOriginal - 1;
                            $oddFinal = ($redu * $cotacaoLive / 100) + $redu + 1;

                            if (in_array($odd->id, $blockedOdds)) {
                                $cotacao = 0;
                            } elseif (isset($alteredOdds[$odd->id])) {
                                $cotacao = round($alteredOdds[$odd->id], 2);
                            } elseif ($oddFinal <= $oddZ) {
                                $cotacao = 0;
                            } elseif ($oddFinal > $oddM) {
                                $cotacao = round($oddM, 2);
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
                                    'type'       => 'pre',
                                ];
                            }
                        }

                        $j++;
                    }
                }
            }

            $cacheKey = "live_hoje_{$siteId}";
            Cache::put($cacheKey, $return, now()->addMinutes(5));

            $totalMatches = collect($return)->sum(fn($l) => count($l['match'] ?? []));
            $this->info("LiveHoje concluído: " . count($return) . " ligas, {$totalMatches} jogos. Cache: {$cacheKey}");

            return $return;

        } catch (\Exception $e) {
            Log::error("Erro no LiveHoje: " . $e->getMessage());
            $this->error("Erro: " . $e->getMessage());
            return [];
        }
    }
}
