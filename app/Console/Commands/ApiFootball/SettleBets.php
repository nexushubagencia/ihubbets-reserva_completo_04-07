<?php

namespace App\Console\Commands\ApiFootball;

use Illuminate\Console\Command;
use App\Models\MatchModel;
use App\Models\Odd;
use App\Models\Bet;
use App\Models\BetItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class SettleBets extends Command
{
    protected $signature = 'apifootball:settle';
    protected $description = 'Liquida apostas finalizando partidas e determinando vencedores';

    private int $settled = 0;
    private int $wins    = 0;
    private int $losses  = 0;
    private int $voids   = 0;

    public function handle(): int
    {
        $apiKey = config('services.apifootball.api_key');
        $apiUrl = config('services.apifootball.api_url');
        $siteId = config('tenant.site_id', 1);

        if (empty($apiKey)) {
            $this->error("API key nao configurada em services.apifootball.api_key");
            return self::FAILURE;
        }

        $this->info("Buscando resultados do dia anterior...");

        $yesterday = Carbon::yesterday()->format('Y-m-d');

        $response = $this->makeRequest($apiKey, $apiUrl . '/fixtures', [
            'date'   => $yesterday,
            'status' => 'FT',
        ]);

        if ($response === null) {
            $this->error("Falha ao buscar resultados da API.");
            return self::FAILURE;
        }

        $body      = $response->json();
        $fixtures  = $body['response'] ?? [];

        $this->info("Resultados encontrados: " . count($fixtures));

        foreach ($fixtures as $fixture) {
            $this->processFixtureResult($fixture, $siteId);
        }

        $this->settlePendingBets($siteId);

        $this->info("Liquidação concluida!");
        $this->info("Partidas atualizadas: {$this->settled}");
        $this->info("Apostas ganhas: {$this->wins}");
        $this->info("Apostas perdidas: {$this->losses}");
        $this->info("Apostas anuladas: {$this->voids}");

        Log::info("apifootball:settle finalizado. Settled: {$this->settled}, Wins: {$this->wins}, Losses: {$this->losses}, Voids: {$this->voids}");

        return self::SUCCESS;
    }

    private function processFixtureResult(array $fixture, int $siteId): void
    {
        $fixtureId  = $fixture['fixture']['id'] ?? 0;
        $status     = $fixture['fixture']['status']['short'] ?? '';
        $homeGoals  = $fixture['goals']['home'] ?? 0;
        $awayGoals  = $fixture['goals']['away'] ?? 0;
        $score      = ($homeGoals !== null && $awayGoals !== null)
            ? $homeGoals . ' - ' . $awayGoals
            : '';

        $match = MatchModel::where('event_id', $fixtureId)
            ->where('site_id', $siteId)
            ->first();

        if (!$match) {
            return;
        }

        $timeStatus = $this->mapApiStatus($status);

        $match->update([
            'score'       => $score,
            'time_status' => $timeStatus,
            'home_true'   => $homeGoals,
            'away_true'   => $awayGoals,
        ]);

        $this->settled++;

        $this->info("Partida {$fixtureId} atualizada: {$score} (status: {$timeStatus})");
    }

    private function settlePendingBets(int $siteId): void
    {
        $pendingItems = BetItem::where('status', 'pending')
            ->whereHas('bet', function ($query) use ($siteId) {
                $query->where('status', 'pending')->where('site_id', $siteId);
            })
            ->get();

        if ($pendingItems->isEmpty()) {
            $this->info("Nenhum item pendente para liquidar.");
            return;
        }

        $groupedItems = $pendingItems->groupBy('bet_id');

        foreach ($groupedItems as $betId => $items) {
            $this->settleBet($betId, $items);
        }
    }

    private function settleBet(int $betId, $items): void
    {
        $bet = Bet::find($betId);

        if (!$bet) {
            return;
        }

        $allResolved = true;
        $allWon      = true;
        $hasLoss     = false;
        $hasVoid     = false;

        foreach ($items as $item) {
            $match = MatchModel::where('event_id', $item->match_id)->first();

            if (!$match) {
                $allResolved = false;
                continue;
            }

            if ($match->time_status !== '3') {
                $allResolved = false;
                continue;
            }

            $result = $this->determineResult($item, $match);
            $item->status = $result;
            $item->save();

            if ($result === 'lost') {
                $allWon = false;
                $hasLoss = true;
            } elseif ($result === 'void') {
                $allWon = false;
                $hasVoid = true;
            } elseif ($result === 'pending') {
                $allResolved = false;
            }
        }

        if (!$allResolved) {
            return;
        }

        if ($hasLoss) {
            $bet->status = 'lost';
            $this->losses++;
        } elseif ($hasVoid) {
            $bet->status = 'void';
            $this->voids++;
        } elseif ($allWon) {
            $bet->status = 'won';
            $this->wins++;

            $payout = 0;
            foreach ($items as $item) {
                $payout += $item->selection_odd;
            }

            $bet->potential_payout = $bet->amount * $payout;
        }

        $bet->save();
    }

    private function determineResult(BetItem $item, MatchModel $match): string
    {
        $home = (int) $match->home_true;
        $away = (int) $match->away_true;
        $totalGoals = $home + $away;

        $selectionLower = strtolower($item->selection_label);
        $marketLower    = strtolower($item->market_name);

        if (str_contains($marketLower, 'vencedor') || str_contains($marketLower, 'match winner') || str_contains($marketLower, '1x2')) {
            if (str_contains($selectionLower, '1') || str_contains($selectionLower, 'home') || str_contains($selectionLower, 'casa')) {
                return $home > $away ? 'won' : ($home === $away ? 'lost' : 'lost');
            }
            if (str_contains($selectionLower, 'x') || str_contains($selectionLower, 'empate') || str_contains($selectionLower, 'draw')) {
                return $home === $away ? 'won' : 'lost';
            }
            if (str_contains($selectionLower, '2') || str_contains($selectionLower, 'away') || str_contains($selectionLower, 'fora')) {
                return $away > $home ? 'won' : ($home === $away ? 'lost' : 'lost');
            }
        }

        if (str_contains($marketLower, 'dupla chance') || str_contains($marketLower, 'double chance')) {
            if (str_contains($selectionLower, '1x') || str_contains($selectionLower, 'home or draw')) {
                return $home >= $away ? 'won' : 'lost';
            }
            if (str_contains($selectionLower, 'x2') || str_contains($selectionLower, 'draw or away')) {
                return $away >= $home ? 'won' : 'lost';
            }
            if (str_contains($selectionLower, '12') || str_contains($selectionLower, 'home or away')) {
                return $home !== $away ? 'won' : 'lost';
            }
        }

        if (str_contains($marketLower, 'over') || str_contains($marketLower, 'mais de')) {
            $line = (float) filter_var($selectionLower, FILTER_SANITIZE_NUMBER_FLOAT);
            if ($line > 0 && $totalGoals > $line) return 'won';
            if ($line > 0) return 'lost';
        }

        if (str_contains($marketLower, 'under') || str_contains($marketLower, 'menos de')) {
            $line = (float) filter_var($selectionLower, FILTER_SANITIZE_NUMBER_FLOAT);
            if ($line > 0 && $totalGoals < $line) return 'won';
            if ($line > 0) return 'lost';
        }

        if (str_contains($marketLower, 'ambas marcam') || str_contains($marketLower, 'both teams')) {
            $btts = $home > 0 && $away > 0;
            if (str_contains($selectionLower, 'sim') || str_contains($selectionLower, 'yes')) {
                return $btts ? 'won' : 'lost';
            }
            if (str_contains($selectionLower, 'nao') || str_contains($selectionLower, 'no')) {
                return !$btts ? 'won' : 'lost';
            }
        }

        return 'pending';
    }

    private function mapApiStatus(string $status): string
    {
        return match ($status) {
            'FT'   => '3',
            'AET'  => '3',
            'PEN'  => '3',
            'PST'  => '-1',
            'CANC' => '-1',
            'ABD'  => '-1',
            'AWD'  => '-1',
            'WO'   => '-1',
            '1H', '2H', 'HT', 'ET', 'P', 'BT', 'LIVE' => '1',
            default => '0',
        };
    }

    private function makeRequest(string $apiKey, string $url, array $params = [])
    {
        $maxRetries = 3;
        $retryCount = 0;

        while ($retryCount < $maxRetries) {
            try {
                $response = Http::withHeaders([
                    'x-apisports-key' => $apiKey,
                    'Accept'          => 'application/json',
                ])->timeout(30)->get($url, $params);

                if ($response->status() === 429) {
                    $retryAfter = (int) $response->header('Retry-After', 10);
                    $this->warn("Rate limit atingido. Aguardando {$retryAfter}s...");
                    Log::warning("API-Football rate limit (settle). Retry after {$retryAfter}s");
                    sleep($retryAfter);
                    $retryCount++;
                    continue;
                }

                if ($response->failed()) {
                    $this->error("API retornou status {$response->status()}");
                    Log::error("API-Football settle error: {$response->status()} - {$response->body()}");
                    return null;
                }

                return $response;

            } catch (\Exception $e) {
                $retryCount++;
                $this->error("Erro na requisicao: {$e->getMessage()}");
                Log::error("API-Football settle request error: {$e->getMessage()}");

                if ($retryCount < $maxRetries) {
                    sleep(2 * $retryCount);
                }
            }
        }

        return null;
    }
}
