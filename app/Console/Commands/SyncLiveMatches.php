<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\BetsApiService;
use App\Models\MatchEvent;
use Illuminate\Support\Facades\Log;

class SyncLiveMatches extends Command
{
    protected $signature = 'ihub:sync-live';
    protected $description = 'Sincroniza placares e estatísticas em tempo real (Ao Vivo)';

    protected $api;

    public function __construct(BetsApiService $api)
    {
        parent::__construct();
        $this->api = $api;
    }

    public function handle()
    {
        $this->info("Iniciando sincronização de jogos AO VIVO...");

        // Busca jogos em destaque (Futebol id=1)
        $response = $this->api->getInPlayEvents(1);

        if (!$response || !isset($response['results'])) {
            $this->error("Falha ao obter dados in-play da API.");
            return;
        }

        $liveEvents = $response['results'];
        $count = 0;

        foreach ($liveEvents as $event) {
            // No InPlay da BetsAPI, o score vem em 'ss', corners em 'stats'
            $match = MatchEvent::where('event_id', $event['id'])->first();

            if ($match) {
                $stats = $event['stats'] ?? [];
                
                $match->update([
                    'score'                    => $event['ss'] ?? $match->score,
                    'time'                     => $event['timer']['tm'] ?? $match->time,
                    'live_status'              => 'Live',
                    'numberOfCornersHome'      => $stats['corners'][0] ?? $match->numberOfCornersHome,
                    'numberOfCornersAway'      => $stats['corners'][1] ?? $match->numberOfCornersAway,
                    'numberOfYellowCardsHome'  => $stats['yellowcards'][0] ?? $match->numberOfYellowCardsHome,
                    'numberOfYellowCardsAway'  => $stats['yellowcards'][1] ?? $match->numberOfYellowCardsAway,
                    'numberOfRedCardsHome'     => $stats['redcards'][0] ?? $match->numberOfRedCardsHome,
                    'numberOfRedCardsAway'     => $stats['redcards'][1] ?? $match->numberOfRedCardsAway,
                ]);
                $count++;
            }
        }

        $this->info("Atualizados {$count} jogos ao vivo.");
    }
}
