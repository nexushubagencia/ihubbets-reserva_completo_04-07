<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Bet;
use App\Models\BetItem;
use App\Models\Game;
use App\Services\BetsApiService;
use App\Services\ResultProcessor;
use Illuminate\Support\Facades\Log;

class SettleApiBets extends Command
{
    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'ihub:settle-api-bets';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Verifica resultados na API e liquida apostas automaticamente';

    protected $api;
    protected $processor;

    public function __construct(BetsApiService $api, ResultProcessor $processor)
    {
        parent::__construct();
        $this->api = $api;
        $this->processor = $processor;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Iniciando liquidação de apostas da API...");

        $processedBets = [];

        BetItem::where('status', 'pending')
            ->whereHas('bet', function($q) {
                $q->where('status', 'pending');
            })
            ->chunk(500, function ($pendingItems) use (&$processedBets) {
                if ($pendingItems->isEmpty()) {
                    return;
                }

                foreach ($pendingItems as $item) {
                    // Evitar reprocessar o mesmo bilhete no mesmo loop se ele tiver vários itens
                    if (isset($processedBets[$item->bet_id])) continue;

                    $this->processItem($item);
                    
                    // Marcar para verificar se o bilhete completo pode ser liquidado
                    $processedBets[$item->bet_id] = true;
                }
            });

        if (empty($processedBets)) {
            $this->info("Nenhum item pendente para processar.");
            return;
        }

        // 2. Liquidar bilhetes (Bets) que tiveram todos os seus itens processados
        foreach (array_keys($processedBets) as $betId) {
            $this->processor->settleBets($betId);
        }

        $this->info("Processamento concluído!");
    }

    protected function processItem(BetItem $item)
    {
        $match = Game::where('event_id', $item->match_id)->first();
        
        if (!$match) {
            $this->warn("Partida não encontrada para o item {$item->id} (Match ID: {$item->match_id})");
            return;
        }

        // Se for jogo manual, o admin resolve. Se for API, buscamos o resultado.
        $result = $this->api->getEventResult($item->match_id);

        if (!$result || !isset($result['results'][0])) {
            $this->warn("Resultado ainda não disponível para o evento {$item->match_id}");
            return;
        }

        $eventData = $result['results'][0];

        // Status 3 na BetsAPI geralmente significa Finalizado
        if ($eventData['ss'] === null && $eventData['time_status'] != '3') {
            return; // Jogo ainda rolando ou sem placar
        }

        $this->info("Processando resultado para {$item->home_team} x {$item->away_team}: {$eventData['ss']}");

        // Atualizar o status do item individual usando o ResultProcessor
        $resultArrays = $this->processor->getWinnersFromApiData($eventData);
        $winners = $resultArrays['winners'] ?? [];
        $returned = $resultArrays['returned'] ?? [];

        // Comparar o palpite do usuário com o vencedor derivado
        if (in_array($item->selection_label, $winners)) {
            $item->status = 'won';
        } elseif (in_array($item->selection_label, $returned)) {
            $item->status = 'returned';
        } else {
            $item->status = 'lost';
        }

        $item->save();
        $this->info("Item {$item->id} marcado como: {$item->status}");
    }
}
