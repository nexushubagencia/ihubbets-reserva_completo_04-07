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
        'football'          => 1,
        'basketball'        => 2,
        'tennis'            => 3,
        'volleyball'        => 4,
        'handball'          => 5,
        'futsal'            => 6,
        'ice_hockey'        => 7,
        'baseball'          => 8,
        'american_football' => 12,
        'esports'           => 15,
        'cricket'           => 18,
        'darts'             => 17,
        'snooker'           => 19,
        'table_tennis'      => 20,
        'boxing'            => 21,
        'mma'               => 22,
    ];

    public const SPORT_NAMES = [
        1  => 'Futebol',
        2  => 'Basquete',
        3  => 'Tênis',
        4  => 'Vôlei',
        5  => 'Handebol',
        6  => 'Futsal',
        7  => 'Hóquei no Gelo',
        8  => 'Baseball',
        12 => 'Futebol Americano',
        15 => 'E-Sports',
        18 => 'Críquete',
        17 => 'Dardos',
        19 => 'Sinuca',
        20 => 'Tênis de Mesa',
        21 => 'Boxe',
        22 => 'MMA/UFC',
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

    /**
     * Retorna partidas futuras (pre-jogo) de um esporte.
     * Usa endpoint /v1/bet365/upcoming que cobre todos os esportes suportados.
     */
    public function getUpcomingBySport(int $sportId, int $page = 1): ?array
    {
        return $this->get('/v1/bet365/upcoming', ['sport_id' => $sportId, 'page' => $page]);
    }

    /**
     * Retorna TODAS as partidas ao vivo de um esporte (formato flat - usado para odds).
     */
    public function getInPlayBySport(int $sportId): ?array
    {
        return $this->get('/v1/bet365/inplay', ['sport_id' => $sportId]);
    }

    /**
     * Retorna TODAS as partidas ao vivo (todos os esportes) em formato flat.
     */
    public function getAllInPlay(): ?array
    {
        return $this->get('/v1/bet365/inplay');
    }

    /**
     * Retorna partidas ao vivo de um esporte em formato estruturado (home/away/league).
     * Ideal para inserir/atualizar jogos ao vivo no banco.
     */
    public function getInPlayFilterBySport(int $sportId, int $page = 1): ?array
    {
        return $this->get('/v1/bet365/inplay_filter', ['sport_id' => $sportId, 'page' => $page]);
    }

    /**
     * Retorna odds pre-jogo completas (v4) usando o FI (id do evento no bet365/upcoming).
     */
    public function getPrematchOdds(int $fi): ?array
    {
        return $this->get('/v4/bet365/prematch', ['FI' => $fi]);
    }

    /**
     * Retorna dados ao vivo de um evento especifico (inclui odds e placar).
     */
    public function getInplayEvent(int $fi): ?array
    {
        return $this->get('/v1/bet365/inplay', ['FI' => $fi]);
    }

    /**
     * Retorna resultado final de um evento.
     */
    public function getEventResult(int $eventId): ?array
    {
        return $this->get('/v1/bet365/result', ['event_id' => $eventId]);
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
