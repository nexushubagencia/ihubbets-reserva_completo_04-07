<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MatchModel;
use App\Models\Palpite;
use Carbon\Carbon;

class AtualiaLiveFutebol extends Command
{
    protected $signature = 'command:liveFutebol';
    protected $description = 'Atualiza partidas de futebol ao vivo via BetsAPI (legado)';
    private $agora;
    private $arr;

    public function __construct()
    {
        parent::__construct();
        $this->agora = Carbon::now();
        $this->arr = [];
    }

    public function handle()
    {
        $token = config('services.betsapi.token', env('TOKEN'));

        $url2 = 'https://api.betsapi.com/v1/betfair/ex/upcoming?sport_id=1&token=' . $token;
        $ch2 = curl_init($url2);
        curl_setopt($ch2, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch2, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
        $matchs2 = curl_exec($ch2);
        $m = json_decode($matchs2);

        if ($matchs2 === false) {
            $info2 = curl_getinfo($ch2);
            curl_close($ch2);
            $this->error('Erro ao buscar partidas: ' . var_export($info2, true));
            return 1;
        }
        curl_close($ch2);

        $matchIds = [];
        if (isset($m->results)) {
            foreach ($m->results as $match) {
                $matchIds[] = $match->id;
            }
        }

        $matchs = Palpite::select('match_id')
            ->groupBy('match_id')
            ->where('match_id', '>', $this->agora)
            ->whereIn('match_id', $matchIds)
            ->get();

        $this->info("Geral " . count($matchIds) . " Jogados " . count($matchs));

        foreach ($matchIds as $matchId) {
            $url = 'https://api.betsapi.com/v1/betfair/ex/event?token=' . $token . '&FI=' . $matchId;
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $data = curl_exec($ch);
            $data = json_decode($data);

            if ($data === false) {
                $info = curl_getinfo($ch);
                curl_close($ch);
                $this->error('Erro ao buscar evento: ' . var_export($info, true));
                continue;
            }
            curl_close($ch);

            if (!isset($data->results[0])) {
                continue;
            }

            $results = count($data->results[0]);
            $mercado = null;

            for ($i = 0; $i < $results; $i++) {
                if ($data->results[0][$i]->type == 'MA') {
                    $mercado = $data->results[0][$i]->NA;
                    $var = str_replace('Fulltime Result', 'Vencedor do Encontro', $mercado);
                }

                if ($data->results[0][$i]->type == 'PA' && $mercado) {
                    $odd = explode("/", $data->results[0][$i]->OD);
                    $oddValue = 1 + ($odd[0] / $odd[1]);

                    if ($mercado == 'Fulltime Result') {
                        $this->arr[] = [
                            'mercado' => $var,
                            'name' => str_replace('1', 'Casa', str_replace('2', 'Fora', str_replace('X', 'Empate', $data->results[0][$i]->N2))),
                            'odd' => $oddValue,
                        ];
                    }
                }
            }
        }

        $this->info("Total processado: " . count($this->arr));
        return 0;
    }
}
