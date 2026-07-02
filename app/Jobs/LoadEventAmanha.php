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

class LoadEventAmanha implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $siteId;

    public function __construct($siteId = null)
    {
        $this->siteId = $siteId ?? config('tenant.site_id', 1);
    }

    public function showAmanha()
    {
        $siteId  = $this->siteId;
        $amanha  = Carbon::tomorrow();
        $hoje    = Carbon::today();

        $config = Configuracao::where('site_id', $siteId)->first();
        $oddZ   = $config->bloquear_odd_abaixo ?? 1.01;
        $oddM   = $config->travar_odd_acima ?? 50.0;

        $disabledSports = [];
        if (($config->op_basquete ?? 'Sim') != 'Sim') $disabledSports[] = 'Basquete';
        if (($config->op_tenis ?? 'Sim') != 'Sim') { $disabledSports[] = 'Tênis'; $disabledSports[] = 'Tenis'; $disabledSports[] = 'Tennis'; }
        if (($config->op_volei ?? 'Sim') != 'Sim') { $disabledSports[] = 'Vôlei'; $disabledSports[] = 'Volei'; }
        if (($config->op_e_sports ?? 'Sim') != 'Sim') $disabledSports[] = 'E-Sports';
        if (($config->op_ufcbox ?? 'Sim') != 'Sim') { $disabledSports[] = 'UFC/Boxe'; $disabledSports[] = 'UFC'; $disabledSports[] = 'Boxe'; }

        $blockMatchIds = BlockMatch::where('site_id', $siteId)->pluck('event_id')->toArray();
        $blockedOdds   = BlockOddMatch::where('site_id', $siteId)->where('status', 0)->pluck('odd_uid')->toArray();
        $alteredOdds   = BlockOddMatch::where('site_id', $siteId)->where('status', 1)->get()->pluck('cotacao', 'odd_id')->toArray();

        $dataAmanha = $amanha->format('Y-m-d');

        $leagues = MatchModel::select('league')
            ->where('site_id', $siteId)
            ->where('date', '>=', $dataAmanha . ' 00:00:00')
            ->where('date', '<=', $dataAmanha . ' 23:59:59')
            ->where('visible', 'Sim')
            ->whereNotIn('sport_name', $disabledSports)
            ->whereNotIn('event_id', $blockMatchIds)
            ->groupBy('league')
            ->orderBy('league', 'asc')
            ->get();

        $return = [];

        foreach ($leagues as $league) {
            $matchs = MatchModel::where('site_id', $siteId)
                ->where('league', $league->league)
                ->where('date', '>=', $dataAmanha . ' 00:00:00')
                ->where('date', '<=', $dataAmanha . ' 23:59:59')
                ->where('visible', 'Sim')
                ->with(['fullOdds' => fn($q) => $q->orderBy('market_name')->orderBy('order')])
                ->get();

            $i = count($return);
            $return[$i]['league'] = $league->league;
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
                    $cotacao = $odd->value;

                    if (in_array($odd->id, $blockedOdds)) {
                        $cotacao = 0;
                    } elseif (isset($alteredOdds[$odd->id])) {
                        $cotacao = round($alteredOdds[$odd->id], 2);
                    } else {
                        if ($cotacao <= $oddZ) $cotacao = 0;
                        elseif ($cotacao > $oddM) $cotacao = round($oddM, 2);
                        else $cotacao = round($cotacao, 2);
                    }

                    $return[$i]['match'][$j]['odds'][$q] = [
                        'id'        => $odd->id,
                        'group_opp' => $odd->market_name,
                        'odd'       => $odd->label,
                        'value'     => $odd->value,
                        'cotacao'   => $cotacao,
                        'type'      => $odd->type,
                    ];
                    $q++;
                }

                if (empty($return[$i]['match'][$j]['odds'])) {
                    for ($q = 0; $q < 3; $q++) {
                        $return[$i]['match'][$j]['odds'][$q] = [
                            'id' => $match->event_id . $match->id . $q,
                            'group_opp' => 'Vencedor do Encontro',
                            'odd' => $q, 'value' => 0, 'cotacao' => 0, 'type' => 'pre',
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
        $data = $this->showAmanha();
        Cache::put("home_amanha_{$this->siteId}", $data, now()->addMinutes(5));
        return $data;
    }
}
