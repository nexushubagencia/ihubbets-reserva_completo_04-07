<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\BetsApiService;
use App\Models\MatchModel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class InsertMatchFutebol extends Command
{
    protected $signature = 'command:insert-match-futebol';
    protected $description = 'Importa partidas de futebol da BetsAPI para o IHUB';

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

            $this->info("Iniciando importação de partidas de Futebol (site_id: {$siteId})...");

            $dateDelete = Carbon::parse($this->today)->subDay()->format('Y-m-d') . ' 23:59:59';

            MatchModel::where('date', '<=', $dateDelete)
                ->where('sport_id', 1)
                ->where('site_id', $siteId)
                ->delete();

            $this->info("Partidas antigas removidas.");

            $response = $this->api->getUpcomingEvents(1, 1);

            if (!$response || !isset($response['results'])) {
                $this->error("Falha ao obter dados da API.");
                return;
            }

            $totalPages = intval(ceil(($response['pager']['total'] ?? 0) / ($response['pager']['per_page'] ?? 30)));
            if ($totalPages < 1) $totalPages = 1;

            $this->info("Total de páginas: {$totalPages}");

            $allEventIds = [];

            for ($i = 1; $i <= $totalPages; $i++) {
                $this->info("Processando página {$i}/{$totalPages}...");

                $pageResponse = $this->api->getUpcomingEvents(1, $i);

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
                        if (isset($match->league)) {
                            $matchCad = MatchModel::create([
                                'site_id'       => $siteId,
                                'event_id'      => $match->id,
                                'our_event_id'  => $match->our_event_id ?? null,
                                'sport_id'      => 1,
                                'sport_name'    => 'Futebol',
                                'league_id'     => $match->league->id ?? 0,
                                'league'        => $match->league->name ?? 'Desconhecida',
                                'league_cc'     => null,
                                'order'         => 0,
                                'visible'       => 'Não',
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
                                $allEventIds[] = $match->id;
                                $this->info("Partida {$match->id} cadastrada.");
                            }
                        }
                    }
                }
            }

            $this->info("Buscando resultados para atualizar imagens e ligas...");

            $matchsAll = MatchModel::select('id', 'event_id', 'date')
                ->where('site_id', $siteId)
                ->where('sport_id', 1)
                ->where('date', '>', $this->agora)
                ->where('our_event_id', '>', 0)
                ->where('time_status', '<=', 1)
                ->get();

            $eventChunks = $matchsAll->pluck('event_id')->chunk(10);

            foreach ($eventChunks as $chunk) {
                $eventIds = $chunk->implode(',');
                $resultData = $this->api->getEventResult(0);

                $url = config('services.bets_api.base_url') . '/v1/bet365/result?event_id=' . $eventIds;
                $token = config('services.bets_api.token');

                try {
                    $response = \Illuminate\Support\Facades\Http::timeout(30)
                        ->get($url, ['token' => $token]);

                    if ($response->successful()) {
                        $data = $response->json();

                        if (isset($data['results'])) {
                            foreach ($data['results'] as $result) {
                                $event = MatchModel::where('event_id', $result['id'])
                                    ->where('site_id', $siteId)
                                    ->first();

                                if (!$event) continue;

                                $dt = Carbon::now('America/Sao_Paulo');
                                $dt->timestamp($result['time'] ?? 0);
                                $date = $dt->format('Y-m-d H:i:s');

                                $matchingDir = $result['matching_dir'] ?? '';

                                if ($matchingDir === '-1') {
                                    $event->update([
                                        'home'            => $result['away']['name'] ?? $event->home,
                                        'away'            => $result['home']['name'] ?? $event->away,
                                        'league_id'       => $result['league']['id'] ?? $event->league_id,
                                        'league_cc'       => $result['league']['cc'] ?? null,
                                        'league'          => $result['league']['name'] ?? $event->league,
                                        'image_id_home'   => $result['away']['image_id'] ?? null,
                                        'image_id_away'   => $result['home']['image_id'] ?? null,
                                        'confronto'       => $date . ($result['league']['name'] ?? '') . ($result['away']['name'] ?? '') . ($result['home']['name'] ?? ''),
                                        'visible'         => 'Sim',
                                    ]);
                                } else {
                                    $event->update([
                                        'home'            => $result['home']['name'] ?? $event->home,
                                        'away'            => $result['away']['name'] ?? $event->away,
                                        'league_id'       => $result['league']['id'] ?? $event->league_id,
                                        'league_cc'       => $result['league']['cc'] ?? null,
                                        'league'          => $result['league']['name'] ?? $event->league,
                                        'image_id_home'   => $result['home']['image_id'] ?? null,
                                        'image_id_away'   => $result['away']['image_id'] ?? null,
                                        'confronto'       => $date . ($result['league']['name'] ?? '') . ($result['home']['name'] ?? '') . ($result['away']['name'] ?? ''),
                                        'visible'         => 'Sim',
                                    ]);
                                }

                                $this->info("Partida event_id={$result['id']} atualizada com imagens e liga.");
                            }
                        }
                    }
                } catch (\Exception $e) {
                    Log::error("Erro ao buscar resultados: " . $e->getMessage());
                    $this->error("Erro ao buscar resultados: " . $e->getMessage());
                }
            }

            $this->info("Importação de Futebol concluída com sucesso!");

        } catch (\Exception $e) {
            Log::error("Erro no InsertMatchFutebol: " . $e->getMessage());
            $this->error("Erro: " . $e->getMessage());
        }
    }
}
