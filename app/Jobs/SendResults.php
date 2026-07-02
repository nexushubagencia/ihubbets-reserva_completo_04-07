<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\MatchModel;
use App\Models\Resultado;
use App\Models\Palpite;
use App\Models\Aposta;
use App\Models\Configuracao;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class SendResults implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 120;

    public function handle()
    {
        $ontem = Carbon::yesterday();
        $agora = Carbon::now();

        $results = Resultado::select('match_id')
            ->groupBy('match_id')
            ->where('created_at', '>=', $ontem->format('Y-m-d H:i:s'))
            ->get();

        $result_send = [];
        foreach ($results as $res) {
            $result_send[] = $res->match_id;
        }

        $matchsAll = Palpite::select('match_id')
            ->groupBy('match_id')
            ->where('match_temp', '<=', $agora->subHour(2)->format('Y-m-d H:i:s'))
            ->where('status', 'Aberto')
            ->whereNotIn('match_id', $result_send)
            ->get();

        foreach ($matchsAll as $match) {
            $token = config('services.betsapi.token', env('TOKEN'));
            $url = 'https://api.betsapi.com/v1/betfair/result?token=' . $token . '&event_id=' . $match->match_id;

            try {
                $response = Http::timeout(30)->get($url);
                $data = json_decode($response->body());

                if (isset($data->results)) {
                    foreach ($data->results as $result) {
                        if (isset($result->time_status) && $result->time_status == 3) {
                            $mt = MatchModel::where('event_id', $match->match_id)->first();
                            if ($mt) {
                                $mt->time_status = 3;
                                $mt->save();
                            }

                            $scores = [];
                            if (isset($result->scores)) {
                                foreach ($result->scores as $key => $value) {
                                    $scores[$key] = [
                                        "home" => $value->home,
                                        "away" => $value->away
                                    ];
                                }
                            }

                            if (isset($scores[2])) {
                                if ($result->matching_dir === "-1") {
                                    $score1 = $scores[2]['away'];
                                    $score2 = $scores[2]['home'];
                                } else {
                                    $score1 = $scores[2]['home'];
                                    $score2 = $scores[2]['away'];
                                }

                                $socore_home_fult_time = $score1;
                                $socore_away_fult_time = $score2;

                                if (isset($scores[1])) {
                                    if ($result->matching_dir === "-1") {
                                        $socore_home_half_time = $scores[1]['away'];
                                        $socore_away_half_time = $scores[1]['home'];
                                    } else {
                                        $socore_home_half_time = $scores[1]['home'];
                                        $socore_away_half_time = $scores[1]['away'];
                                    }
                                } else {
                                    $socore_home_half_time = $score1;
                                    $socore_away_half_time = $score2;
                                }

                                $scoreHome2and = $score1 - $socore_home_half_time;
                                $scoreAway2and = $score2 - $socore_away_half_time;

                                $score_global = $score1 . "-" . $score2 . ' (' . $socore_home_half_time . '-' . $socore_away_half_time . ')';

                                if (count(Resultado::where('match_id', $match->match_id)->get()) == 0) {
                                    $this->generateResults($match->match_id, $score_global, $socore_home_fult_time, $socore_away_fult_time, $socore_home_half_time, $socore_away_half_time, $scoreHome2and, $scoreAway2and);
                                }
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                \Log::error('Erro ao processar resultado: ' . $e->getMessage());
            }
        }
    }

    private function generateResults($match_id, $score_global, $home_full, $away_full, $home_half, $away_half, $home_2nd, $away_2nd)
    {
        // Vencedor do Encontro
        if ($home_full > $away_full) {
            Resultado::create(['match_id' => $match_id, 'scores' => $score_global, 'resultado' => 'Casa']);
            Resultado::create(['match_id' => $match_id, 'scores' => $score_global, 'resultado' => 'Casa ou Empate']);
            Resultado::create(['match_id' => $match_id, 'scores' => $score_global, 'resultado' => 'Casa ou Fora']);
        }
        if ($home_full == $away_full) {
            Resultado::create(['match_id' => $match_id, 'scores' => $score_global, 'resultado' => 'Empate']);
            Resultado::create(['match_id' => $match_id, 'scores' => $score_global, 'resultado' => 'Casa ou Empate']);
            Resultado::create(['match_id' => $match_id, 'scores' => $score_global, 'resultado' => 'Empate ou Fora']);
        }
        if ($home_full < $away_full) {
            Resultado::create(['match_id' => $match_id, 'scores' => $score_global, 'resultado' => 'Fora']);
            Resultado::create(['match_id' => $match_id, 'scores' => $score_global, 'resultado' => 'Empate ou Fora']);
            Resultado::create(['match_id' => $match_id, 'scores' => $score_global, 'resultado' => 'Casa ou Fora']);
        }

        // 1º Tempo
        if ($home_half > $away_half) {
            Resultado::create(['match_id' => $match_id, 'scores' => $score_global, 'resultado' => 'Casa (1T)']);
            Resultado::create(['match_id' => $match_id, 'scores' => $score_global, 'resultado' => 'Casa ou Empate (1T)']);
        }
        if ($home_half == $away_half) {
            Resultado::create(['match_id' => $match_id, 'scores' => $score_global, 'resultado' => 'Empate (1T)']);
        }
        if ($home_half < $away_half) {
            Resultado::create(['match_id' => $match_id, 'scores' => $score_global, 'resultado' => 'Fora (1T)']);
            Resultado::create(['match_id' => $match_id, 'scores' => $score_global, 'resultado' => 'Empate ou Fora (1T)']);
        }

        // 2º Tempo
        if ($home_2nd > $away_2nd) {
            Resultado::create(['match_id' => $match_id, 'scores' => $score_global, 'resultado' => 'Casa (2T)']);
        }
        if ($home_2nd == $away_2nd) {
            Resultado::create(['match_id' => $match_id, 'scores' => $score_global, 'resultado' => 'Empate (2T)']);
        }
        if ($home_2nd < $away_2nd) {
            Resultado::create(['match_id' => $match_id, 'scores' => $score_global, 'resultado' => 'Fora (2T)']);
        }

        // Placar Exato
        Resultado::create(['match_id' => $match_id, 'scores' => $score_global, 'resultado' => $home_full . '-' . $away_full]);
        Resultado::create(['match_id' => $match_id, 'scores' => $score_global, 'resultado' => $home_half . '-' . $away_half . ' (1T)']);

        // Total de Gols
        $total_gols = $home_full + $away_full;
        if ($total_gols < 1) Resultado::create(['match_id' => $match_id, 'scores' => $score_global, 'resultado' => 'Menos de 0.5']);
        if ($total_gols < 2) Resultado::create(['match_id' => $match_id, 'scores' => $score_global, 'resultado' => 'Menos de 1.5']);
        if ($total_gols < 3) Resultado::create(['match_id' => $match_id, 'scores' => $score_global, 'resultado' => 'Menos de 2.5']);
        if ($total_gols > 0) Resultado::create(['match_id' => $match_id, 'scores' => $score_global, 'resultado' => 'Mais de 0.5']);
        if ($total_gols > 1) Resultado::create(['match_id' => $match_id, 'scores' => $score_global, 'resultado' => 'Mais de 1.5']);
        if ($total_gols > 2) Resultado::create(['match_id' => $match_id, 'scores' => $score_global, 'resultado' => 'Mais de 2.5']);

        // Par/Ímpar
        if ($total_gols % 2 == 0) {
            Resultado::create(['match_id' => $match_id, 'scores' => $score_global, 'resultado' => 'Par']);
        } else {
            Resultado::create(['match_id' => $match_id, 'scores' => $score_global, 'resultado' => 'Ímpar']);
        }

        // Ambas Marcam
        if ($home_full > 0 && $away_full > 0) {
            Resultado::create(['match_id' => $match_id, 'scores' => $score_global, 'resultado' => 'Sim']);
        } else {
            Resultado::create(['match_id' => $match_id, 'scores' => $score_global, 'resultado' => 'Não']);
        }
    }
}
