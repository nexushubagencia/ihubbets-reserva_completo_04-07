<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\Odd;
use App\Models\Configuracao;
use Illuminate\Support\Facades\Cache;

class LoadMatchLive implements ShouldQueue
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
        $siteId = $this->siteId;
        $id     = $this->matchId;

        $config      = Configuracao::where('site_id', $siteId)->first();
        $cotacaoLive = $config->cotacao_live ?? 0;
        $oddZ        = $config->bloquear_odd_abaixo ?? 1.01;

        $mercados = Odd::select('market_name')
            ->where('event_id', $id)
            ->groupBy('market_name')
            ->orderBy('market_name')
            ->get();

        $arr = [];

        foreach ($mercados as $mercado) {
            $odds = Odd::where('event_id', $id)
                ->where('market_name', $mercado->market_name)
                ->orderBy('order')
                ->get();

            $i = count($arr);
            $arr[$i]['match_id'] = $id;
            $arr[$i]['name']     = $mercado->market_name;

            $j = 0;
            foreach ($odds as $odd) {
                $redu     = $odd->value - 1;
                $oddFinal = ($redu * $cotacaoLive / 100) + $redu + 1;

                if ($oddFinal <= $oddZ) {
                    $cota = 0;
                } else {
                    $cota = round($oddFinal, 2);
                }

                $arr[$i]['odds'][$j] = [
                    'id'              => $odd->id,
                    'group_opp'       => $odd->market_name,
                    'odd'             => $odd->label,
                    'type'            => $odd->type,
                    'cotacaoOriginal' => $odd->value,
                    'cotacao'         => $cota,
                ];
                $j++;
            }
        }

        $arr = array_values(array_filter($arr));

        $cacheKey = "match_live_{$id}_{$siteId}";
        Cache::put($cacheKey, $arr, now()->addMinutes(1));

        return $arr;
    }
}
