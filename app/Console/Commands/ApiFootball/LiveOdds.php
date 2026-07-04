<?php

namespace App\Console\Commands\ApiFootball;

use Illuminate\Console\Command;
use App\Models\MatchModel;
use App\Models\Odd;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\RateLimiterService;

class LiveOdds extends Command
{
    protected $signature = 'apifootball:live';
    protected $description = 'Atualiza partidas ao vivo e odds em tempo real da API-Football';

    private int $matchesUpdated = 0;
    private int $oddsUpdated    = 0;

    public function handle(): int
    {
        $apiKey = config('services.apifootball.api_key');
        $apiUrl = config('services.apifootball.api_url');
        $siteId = config('tenant.site_id', 1);

        if (empty($apiKey)) {
            $this->error("API key nao configurada em services.apifootball.api_key");
            return self::FAILURE;
        }

        if (!RateLimiterService::canMakeRequest()) {
            $this->warn("Rate limit atingido. Pulando atualização ao vivo.");
            return self::SUCCESS;
        }

        $this->info("Buscando partidas ao vivo...");

        $response = $this->makeRequest($apiKey, $apiUrl . '/fixtures', [
            'live' => 'all',
        ]);

        if ($response === null) {
            $this->error("Falha ao buscar dados ao vivo da API.");
            return self::FAILURE;
        }

        $body     = $response->json();
        $fixtures = $body['response'] ?? [];

        $this->info("Partidas ao vivo encontradas: " . count($fixtures));

        foreach ($fixtures as $fixture) {
            $this->processLiveFixture($fixture, $siteId);
        }

        $this->info("Concluido! Partidas atualizadas: {$this->matchesUpdated} | Odds atualizadas: {$this->oddsUpdated}");
        Log::info("apifootball:live finalizado. Matches: {$this->matchesUpdated}, Odds: {$this->oddsUpdated}");

        return self::SUCCESS;
    }

    private function processLiveFixture(array $fixture, int $siteId): void
    {
        $fixtureId = $fixture['fixture']['id'] ?? 0;
        $status    = $fixture['fixture']['status']['short'] ?? '';
        $elapsed   = $fixture['fixture']['status']['elapsed'] ?? 0;
        $homeGoals = $fixture['goals']['home'] ?? 0;
        $awayGoals = $fixture['goals']['away'] ?? 0;
        $score     = ($homeGoals !== null && $awayGoals !== null)
            ? $homeGoals . ' - ' . $awayGoals
            : '';

        $timeStatus = $this->mapApiStatus($status);

        $match = MatchModel::where('event_id', $fixtureId)
            ->where('site_id', $siteId)
            ->first();

        if (!$match) {
            return;
        }

        $updateData = [
            'score'       => $score,
            'time_status' => $timeStatus,
            'time'        => $elapsed,
            'home_true'   => $homeGoals,
            'away_true'   => $awayGoals,
            'live_status' => $timeStatus === '3' ? null : 'Live',
        ];

        $match->update($updateData);
        $this->matchesUpdated++;

        $this->info("Live: {$match->home} x {$match->away} | Placar: {$score} | Min: {$elapsed}' | Status: {$timeStatus}");

        if ($timeStatus !== '3') {
            $this->processLiveOdds($fixture, $fixtureId);
        }
    }

    private function processLiveOdds(array $fixture, int $fixtureId): void
    {
        $bookmakers = $fixture['bookmakers'] ?? [];

        if (empty($bookmakers)) {
            return;
        }

        foreach ($bookmakers as $bookmaker) {
            $markets = $bookmaker['bets'] ?? [];

            foreach ($markets as $market) {
                $marketName = $market['name'] ?? '';
                $values     = $market['values'] ?? [];

                foreach ($values as $index => $oddValue) {
                    $label = $oddValue['value'] ?? '';
                    $odd   = $oddValue['odd'] ?? null;

                    if ($odd === null || $odd <= 1.0) {
                        continue;
                    }

                    $type = $this->deriveOddType($marketName, $label, $index);

                    $existing = Odd::where('event_id', $fixtureId)
                        ->where('market_name', $marketName)
                        ->where('label', $label)
                        ->first();

                    if ($existing) {
                        if ((float) $existing->value !== (float) $odd) {
                            $existing->update([
                                'value' => $odd,
                                'type'  => $type,
                            ]);
                            $this->oddsUpdated++;
                        }
                    } else {
                        Odd::create([
                            'event_id'    => $fixtureId,
                            'market_name' => $marketName,
                            'label'       => $label,
                            'value'       => $odd,
                            'type'        => $type,
                            'status'      => 'active',
                        ]);
                        $this->oddsUpdated++;
                    }
                }
            }
        }
    }

    private function deriveOddType(string $marketName, string $label, int $index): string
    {
        $marketLower = strtolower($marketName);
        $labelLower  = strtolower($label);

        if ($marketLower === 'match winner' || $marketLower === 'vencedor do encontro') {
            return match ($index) {
                0 => '1',
                1 => 'X',
                2 => '2',
                default => '1',
            };
        }

        if (str_contains($marketLower, 'over/under') || str_contains($marketLower, 'total goals')) {
            if (str_contains($labelLower, 'over')) return 'over';
            if (str_contains($labelLower, 'under')) return 'under';
        }

        if (str_contains($labelLower, 'home') || str_contains($labelLower, 'casa')) return '1';
        if (str_contains($labelLower, 'draw') || str_contains($labelLower, 'empate')) return 'X';
        if (str_contains($labelLower, 'away') || str_contains($labelLower, 'fora')) return '2';

        return (string) $index;
    }

    private function mapApiStatus(string $status): string
    {
        return match ($status) {
            '1H', '2H', 'HT', 'ET', 'P', 'BT', 'LIVE' => '1',
            'FT', 'AET', 'PEN' => '3',
            'PST', 'CANC', 'ABD', 'AWD', 'WO' => '-1',
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
                    Log::warning("API-Football rate limit (live). Retry after {$retryAfter}s");
                    sleep($retryAfter);
                    $retryCount++;
                    continue;
                }

                if ($response->failed()) {
                    $this->error("API retornou status {$response->status()}");
                    Log::error("API-Football live error: {$response->status()} - {$response->body()}");
                    return null;
                }

                return $response;

            } catch (\Exception $e) {
                $retryCount++;
                $this->error("Erro na requisicao: {$e->getMessage()}");
                Log::error("API-Football live request error: {$e->getMessage()}");

                if ($retryCount < $maxRetries) {
                    sleep(2 * $retryCount);
                }
            }
        }

        return null;
    }
}
