<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\BetsApiService;
use App\Models\MatchModel;
use App\Models\Configuracao;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class InsertMatchVolleyBall extends Command
{
    protected $signature = 'command:insert-match-volleyball';
    protected $description = 'Importa partidas de voleibol da BetsAPI para o IHUB';

    protected $api;

    private $agora;
    private $today;

    public function __construct(BetsApiService $api)
    {
        parent::__construct();
        $this->api = $api;
        $this->agora = Carbon::now()->format('Y-m-d H:i:s');
        $this->today = Carbon::today()->format('Y-m-d');
    }

    public function handle()
    {
        try {
            $siteId = config('tenant.site_id', 1);

            $settings = Configuracao::where('site_id', $siteId)->first();

            if (!$settings || $settings->op_volei !== 'Sim') {
                $this->info("Volei desativado para este site.");
                return;
            }

            $this->info("Iniciando importação de partidas de Volei (site_id: {$siteId})...");

            $dateDelete = Carbon::parse($this->today)->subDay()->format('Y-m-d') . ' 23:59:59';

            MatchModel::where('date', '<=', $dateDelete)
                ->where('sport_id', 91)
                ->where('site_id', $siteId)
                ->delete();

            $this->info("Partidas antigas removidas.");

            $response = $this->api->getUpcomingEvents(91, 1);

            if (!$response || !isset($response['results'])) {
                $this->error("Falha ao obter dados da API.");
                return;
            }

            $totalPages = intval(ceil(($response['pager']['total'] ?? 0) / ($response['pager']['per_page'] ?? 30)));
            if ($totalPages < 1) $totalPages = 1;

            $this->info("Total de páginas: {$totalPages}");

            for ($i = 1; $i <= $totalPages; $i++) {
                $this->info("Processando página {$i}/{$totalPages}...");

                $pageResponse = $this->api->getUpcomingEvents(91, $i);

                if (!$pageResponse || !isset($pageResponse['results'])) {
                    $this->error("Falha ao obter dados da página {$i}.");
                    continue;
                }

                foreach ($pageResponse['results'] as $match) {
                    $dt = Carbon::now('America/Sao_Paulo');
                    $dt->timestamp($match->time);
                    $date = $dt->format('Y-m-d H:i:s');

                    $existingMatch = MatchModel::where('event_id', $match->id)
                        ->where('site_id', $siteId)
                        ->first();

                    if ($existingMatch) {
                        $existingMatch->update([
                            'date' => $date,
                            'our_event_id' => $match->our_event_id ?? null,
                        ]);
                        $this->info("Partida {$match->id} atualizada.");
                    } else {
                        $matchCad = MatchModel::create([
                            'site_id'       => $siteId,
                            'event_id'      => $match->id,
                            'our_event_id'  => $match->our_event_id ?? null,
                            'sport_id'      => 91,
                            'sport_name'    => 'Volei',
                            'league_id'     => $match->league->id ?? 0,
                            'league'        => $match->league->name ?? 'Desconhecida',
                            'league_cc'     => null,
                            'order'         => 0,
                            'visible'       => 'Sim',
                            'schedule'      => 0,
                            'time_status'   => $match->time_status ?? '',
                            'date'          => $date,
                            'confronto'     => $date . ($match->league->name ?? '') . ($match->home->name ?? '') . ($match->away->name ?? ''),
                            'home'          => $match->home->name ?? 'Time A',
                            'image_id_home' => null,
                            'away'          => $match->away->name ?? 'Time B',
                            'image_id_away' => null,
                            'live_status'   => null,
                        ]);

                        if ($matchCad) {
                            $this->fetchAndSetResultData($matchCad, $match->id);
                            $this->info("Partida {$match->id} cadastrada.");
                        }
                    }
                }
            }

            $this->info("Importação de Volei concluída com sucesso!");

        } catch (\Exception $e) {
            Log::error("Erro no InsertMatchVolleyBall: " . $e->getMessage());
            $this->error("Erro: " . $e->getMessage());
        }
    }

    private function fetchAndSetResultData(MatchModel $matchCad, int $eventId): void
    {
        try {
            $url = config('services.bets_api.base_url') . '/v1/bet365/result';
            $token = config('services.bets_api.token');

            $response = \Illuminate\Support\Facades\Http::timeout(30)
                ->get($url, [
                    'token'    => $token,
                    'event_id' => $eventId,
                ]);

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['results'])) {
                    foreach ($data['results'] as $result) {
                        if (isset($result['home']['image_id'])) {
                            $matchCad->image_id_home = $result['home']['image_id'];
                        }
                        if (isset($result['away']['image_id'])) {
                            $matchCad->image_id_away = $result['away']['image_id'];
                        }
                        $matchCad->league_cc = $result['league']['cc'] ?? null;
                    }
                    $matchCad->save();
                }
            }
        } catch (\Exception $e) {
            Log::error("Erro ao buscar resultado para event_id={$eventId}: " . $e->getMessage());
        }
    }
}
