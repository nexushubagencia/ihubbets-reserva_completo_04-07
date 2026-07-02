<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BetsApiService
{
    protected $token;
    protected $baseUrl;

    public function __construct()
    {
        $this->token = config('services.bets_api.token');
        $this->baseUrl = config('services.bets_api.base_url');
    }

    /**
     * Busca partidas futuras de um esporte específico
     */
    public function getUpcomingEvents(int $sportId = 1, int $page = 1)
    {
        return $this->get('/v1/bet365/upcoming', [
            'sport_id' => $sportId,
            'page' => $page
        ]);
    }

    /**
     * Busca partidas ao vivo
     */
    public function getInPlayEvents(int $sportId = 1)
    {
        return $this->get('/v1/bet365/inplay', [
            'sport_id' => $sportId
        ]);
    }

    /**
     * Busca o resumo de Odds de um evento específico
     */
    public function getEventOdds(int $eventId)
    {
        return $this->get('/v2/event/odds/summary', [
            'event_id' => $eventId
        ]);
    }

    /**
     * Busca o resultado final de um evento para liquidação
     */
    public function getEventResult(int $eventId)
    {
        return $this->get('/v1/bet365/result', [
            'event_id' => $eventId
        ]);
    }

    /**
     * Busca a lista de ligas disponíveis
     */
    public function getLeagues(int $sportId = 1)
    {
        return $this->get('/v1/league', [
            'sport_id' => $sportId
        ]);
    }

    /**
     * Método base para requisições GET com controle de erros
     */
    protected function get(string $endpoint, array $params = [])
    {
        $params['token'] = $this->token;

        try {
            $response = Http::timeout(30)->get($this->baseUrl . $endpoint, $params);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error("BetsAPI Error: " . $response->status() . " on $endpoint", [
                'params' => $params,
                'body' => $response->body()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::critical("BetsAPI Exception: " . $e->getMessage(), [
                'endpoint' => $endpoint
            ]);
            return null;
        }
    }
}
