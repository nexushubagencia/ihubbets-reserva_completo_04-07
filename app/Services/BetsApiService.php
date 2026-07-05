<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BetsApiService
{
    private string $token;
    private string $baseUrl;
    private int $requestCount = 0;
    private int $maxRetries = 3;

    public const SPORT_IDS = [
        'football'   => 1,
        'basketball' => 2,
        'tennis'     => 3,
        'volleyball' => 4,
        'handball'   => 5,
        'futsal'     => 6,
        'ice_hockey' => 7,
        'baseball'   => 8,
        'american_football' => 12,
        'esports'    => 15,
        'boxing'     => 21,
        'mma'        => 22,
        'cricket'    => 18,
        'darts'      => 17,
        'snooker'    => 19,
        'table_tennis' => 20,
    ];

    public const SPORT_NAMES = [
        1 => 'Futebol', 2 => 'Basquete', 3 => 'Tênis', 4 => 'Vôlei',
        5 => 'Handebol', 6 => 'Futsal', 7 => 'Hóquei no Gelo', 8 => 'Baseball',
        12 => 'Futebol Americano', 15 => 'E-Sports', 21 => 'Boxe',
        22 => 'MMA/UFC', 18 => 'Críquete', 17 => 'Dardos', 19 => 'Sinuca',
        20 => 'Tênis de Mesa',
    ];

    public function __construct()
    {
        $this->token = config('services.bets_api.token', '');
        $this->baseUrl = config('services.bets_api.base_url', 'https://api.b365api.com');
    }

    public function isConfigured(): bool
    {
        return !empty($this->token);
    }

    public function testConnection(): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'message' => 'Token não configurado'];
        }

        try {
            $response = $this->get('/v1/bet365/inplay', ['sport_id' => 1]);
            if ($response && isset($response['success']) && $response['success'] == 1) {
                return [
                    'success' => true,
                    'message' => 'Conexão OK',
                    'live_count' => count($response['results'] ?? []),
                ];
            }
            return ['success' => false, 'message' => 'Token inválido ou expirado'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Erro: ' . $e->getMessage()];
        }
    }

    public function getUpcomingBySport(int $sportId, int $page = 1): ?array
    {
        $endpoints = [
            1 => '/v1/bet365/upcoming',
            2 => '/v1/bet365/upcoming',
            3 => '/v1/bet365/upcoming',
            4 => '/v1/bet365/upcoming',
        ];

        $endpoint = $endpoints[$sportId] ?? '/v1/bet365/upcoming';
        return $this->get($endpoint, ['sport_id' => $sportId, 'page' => $page]);
    }

    public function getInPlayBySport(int $sportId): ?array
    {
        return $this->get('/v1/bet365/inplay', ['sport_id' => $sportId]);
    }

    public function getAllInPlay(): ?array
    {
        return $this->get('/v1/bet365/inplay');
    }

    public function getPrematchOdds(int $fi): ?array
    {
        return $this->get('/v4/bet365/prematch', ['FI' => $fi]);
    }

    public function getInplayOdds(int $fi): ?array
    {
        return $this->get('/v1/bet365/inplay', ['FI' => $fi]);
    }

    public function getEventOdds(int $eventId): ?array
    {
        return $this->get('/v2/event/odds/summary', ['event_id' => $eventId]);
    }

    public function getEventResult(int $eventId): ?array
    {
        return $this->get('/v1/bet365/result', ['event_id' => $eventId]);
    }

    public function getLeagues(int $sportId = 1): ?array
    {
        return $this->get('/v1/league', ['sport_id' => $sportId]);
    }

    public function getOddsSummary(int $fi): ?array
    {
        return $this->get('/v2/event/odds/summary', ['FI' => $fi]);
    }

    public function getRequestCount(): int
    {
        return $this->requestCount;
    }

    private function get(string $endpoint, array $params = []): ?array
    {
        $params['token'] = $this->token;
        $retryCount = 0;

        while ($retryCount < $this->maxRetries) {
            try {
                $this->requestCount++;
                $response = Http::timeout(30)
                    ->withHeaders(['Accept' => 'application/json'])
                    ->get($this->baseUrl . $endpoint, $params);

                if ($response->status() === 429) {
                    $retryAfter = (int) $response->header('Retry-After', 60);
                    Log::warning("BetsAPI rate limit. Retry after {$retryAfter}s");
                    sleep($retryAfter);
                    $retryCount++;
                    continue;
                }

                if ($response->successful()) {
                    $data = $response->json();
                    if (isset($data['success']) && $data['success'] == 1) {
                        return $data;
                    }
                    Log::warning("BetsAPI: success=0 on {$endpoint}", ['body' => $data]);
                    return $data;
                }

                Log::error("BetsAPI HTTP {$response->status()} on {$endpoint}");
                return null;

            } catch (\Exception $e) {
                $retryCount++;
                Log::error("BetsAPI exception on {$endpoint}: " . $e->getMessage());
                if ($retryCount < $this->maxRetries) {
                    sleep(2 * $retryCount);
                }
            }
        }

        return null;
    }
}
