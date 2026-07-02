<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\BetsApiService;
use App\Models\MatchEvent;
use App\Models\Odd;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SyncMatchesFromApi extends Command
{
    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'ihub:sync-matches {--sport=1} {--days=3}';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Sincroniza partidas e odds da BetsAPI para o banco de dados master';

    protected $api;

    public function __construct(BetsApiService $api)
    {
        parent::__construct();
        $this->api = $api;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $sportId = $this->option('sport');
        $this->info("Iniciando sincronização para Sport ID: {$sportId}...");

        $response = $this->api->getUpcomingEvents($sportId);

        if (!$response || !isset($response['results'])) {
            $this->error("Falha ao obter dados da API.");
            return;
        }

        $events = $response['results'];
        $bar = $this->output->createProgressBar(count($events));
        $bar->start();

        foreach ($events as $event) {
            DB::transaction(function () use ($event, $sportId) {
                // 1. Upsert na Partida
                $match = MatchEvent::updateOrCreate(
                    ['event_id' => $event['id']],
                    [
                        'our_event_id'  => $event['our_event_id'] ?? null,
                        'sport_id'      => $sportId,
                        'league_id'     => $event['league']['id'] ?? 0,
                        'league'        => $event['league']['name'] ?? 'Desconhecida',
                        'league_cc'     => $event['league']['cc'] ?? null,
                        'home'          => $event['home']['name'] ?? 'Time A',
                        'away'          => $event['away']['name'] ?? 'Time B',
                        'time'          => $event['time'],
                        'date'          => Carbon::createFromTimestamp($event['time']),
                        'time_status'   => $event['time_status'],
                        'visible'       => 'Sim',
                        'confronto'     => ($event['home']['name'] ?? '') . ' x ' . ($event['away']['name'] ?? '')
                    ]
                );

                // 2. Fetch e Sync de Odds (Apenas se não for live ou se for refresh forçado)
                // Nota: Em produção, isso pode ser feito via Job separado para não travar o sync
                $this->syncOdds($match);
            });

            $bar->advance();
        }

        $bar->finish();
        $this->info("\nSincronização concluída com sucesso!");
    }

    protected function syncOdds(MatchEvent $match)
    {
        $oddsData = $this->api->getEventOdds($match->event_id);

        if ($oddsData && isset($oddsData['results']['odds'])) {
            $mainOdds = $oddsData['results']['odds'];

            // Exemplo: Mapeando 1x2 (Vencedor do encontro)
            if (isset($mainOdds['1_1'])) {
                foreach ($mainOdds['1_1'] as $odd) {
                    Odd::updateOrCreate(
                        [
                            'event_id' => $match->event_id,
                            'market_name' => '1X2',
                            'label' => $odd['name']
                        ],
                        [
                            'value' => $odd['odds'],
                            'type' => 'main'
                        ]
                    );
                }
            }
        }
    }
}
