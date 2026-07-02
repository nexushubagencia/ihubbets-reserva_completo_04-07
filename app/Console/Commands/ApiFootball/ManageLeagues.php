<?php

namespace App\Console\Commands\ApiFootball;

use Illuminate\Console\Command;
use App\Models\ApifootballLeague;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ManageLeagues extends Command
{
    protected $signature = 'apifootball:manage_leagues
                            {--list : Lista todas as ligas disponiveis}
                            {--enable= : Ativa uma liga pelo league_id}
                            {--disable= : Desativa uma liga pelo league_id}';
    protected $description = 'Gerencia ligas da API-Football (api-sports.io) no IHUB';

    private const SPORT_MAP = [
        'football'   => 1,
        'basketball' => 2,
        'volleyball' => 3,
        'mma'        => 4,
    ];

    public function handle(): int
    {
        $apiKey = config('services.apifootball.api_key');
        $apiUrl = config('services.apifootball.api_url');
        $siteId = config('tenant.site_id', 1);

        if (empty($apiKey)) {
            $this->error("API key nao configurada em services.apifootball.api_key");
            return self::FAILURE;
        }

        if ($this->option('list')) {
            return $this->listLeagues($apiKey, $apiUrl, $siteId);
        }

        if ($leagueId = $this->option('enable')) {
            return $this->toggleLeague((int) $leagueId, true, $apiKey, $apiUrl, $siteId);
        }

        if ($leagueId = $this->option('disable')) {
            return $this->toggleLeague((int) $leagueId, false, $apiKey, $apiUrl, $siteId);
        }

        $this->printUsage();
        return self::SUCCESS;
    }

    private function listLeagues(string $apiKey, string $apiUrl, int $siteId): int
    {
        $this->info("Buscando ligas disponiveis na API-Football (api-sports.io)...");

        $response = $this->makeRequest($apiKey, $apiUrl . '/leagues', []);

        if ($response === null) {
            $this->error("Falha ao buscar ligas da API-Football.");
            return self::FAILURE;
        }

        $body     = $response->json();
        $leagues  = $body['response'] ?? [];
        $remaining = $body['remaining'] ?? 'N/A';

        $this->info("Requisicoes restantes hoje: {$remaining}");

        if (empty($leagues)) {
            $this->warn("Nenhuma liga encontrada.");
            return self::SUCCESS;
        }

        $this->newLine();
        $this->info("=== FUTEBOL (API-Football) ===");

        $rows = [];

            foreach ($leagues as $leagueData) {
                $league = $leagueData['league'] ?? [];
                $country = $leagueData['country'] ?? [];

                $leagueId   = $league['id'] ?? 0;
                $leagueName = $league['name'] ?? 'Desconhecida';
                $countryName = $country['name'] ?? 'Desconhecido';

                $dbLeague = ApifootballLeague::where('league_id', $leagueId)
                    ->where('site_id', $siteId)
                    ->first();

                $active = $dbLeague ? ($dbLeague->active ? 'Sim' : 'Nao') : 'Nao encontrado';

                $rows[] = [
                    $leagueId,
                    $leagueName,
                    $countryName,
                    $active,
                ];
            }

        $this->table(
            ['ID', 'Liga', 'Pais', 'Ativo'],
            $rows
        );

        $this->newLine();
        $this->info("Use --enable={id} para ativar ou --disable={id} para desativar uma liga.");

        return self::SUCCESS;
    }

    private function toggleLeague(int $leagueId, bool $active, string $apiKey, string $apiUrl, int $siteId): int
    {
        $action = $active ? 'Ativando' : 'Desativando';
        $this->info("{$action} liga ID: {$leagueId}...");

        $leagueData = $this->fetchLeagueInfo($apiKey, $apiUrl, $leagueId);

        $sportName = 'football';
        $name      = 'Liga ' . $leagueId;
        $country   = '';

        if ($leagueData) {
            $sportName = $this->detectSport($leagueData);
            $name      = $leagueData['league']['name'] ?? $name;
            $country   = $leagueData['country']['name'] ?? '';
        }

        $sportId = self::SPORT_MAP[$sportName] ?? 1;

        $existing = ApifootballLeague::where('league_id', $leagueId)
            ->where('site_id', $siteId)
            ->first();

        if ($existing) {
            $existing->update([
                'active' => $active,
                'name'   => $name,
                'country' => $country,
                'sport'  => $sportName,
                'season' => date('Y'),
            ]);

            $this->info("Liga '{$name}' atualizada: " . ($active ? 'ATIVADA' : 'DESATIVADA'));
        } else {
            ApifootballLeague::create([
                'league_id' => $leagueId,
                'name'      => $name,
                'country'   => $country,
                'sport'     => $sportName,
                'active'    => $active,
                'site_id'   => $siteId,
                'season'    => date('Y'),
            ]);

            $this->info("Liga '{$name}' criada e " . ($active ? 'ATIVADA' : 'DESATIVADA'));
        }

        Log::info("apifootball:manage_leagues - Liga {$leagueId} ({$name}) " . ($active ? 'ativada' : 'desativada'));

        return self::SUCCESS;
    }

    private function fetchLeagueInfo(string $apiKey, string $apiUrl, int $leagueId): ?array
    {
        $response = $this->makeRequest($apiKey, $apiUrl . '/leagues', [
            'id' => $leagueId,
        ]);

        if ($response === null) {
            return null;
        }

        $body    = $response->json();
        $leagues = $body['response'] ?? [];

        return $leagues[0] ?? null;
    }

    private function detectSport(array $leagueData): string
    {
        $type = strtolower($leagueData['league']['type'] ?? '');

        if (str_contains($type, 'cup') || str_contains($type, 'league')) {
            return 'football';
        }

        return 'football';
    }

    private function printUsage(): void
    {
        $this->newLine();
        $this->info("=== Gerenciador de Ligas API-Football ===");
        $this->newLine();
        $this->info("Uso:");
        $this->line("  php artisan apifootball:manage_leagues --list");
        $this->line("    Lista todas as ligas disponiveis e seu status.");
        $this->newLine();
        $this->line("  php artisan apifootball:manage_leagues --enable={league_id}");
        $this->line("    Ativa uma liga pelo ID.");
        $this->newLine();
        $this->line("  php artisan apifootball:manage_leagues --disable={league_id}");
        $this->line("    Desativa uma liga pelo ID.");
        $this->newLine();
        $this->info("Exemplos:");
        $this->line("  php artisan apifootball:manage_leagues --list");
        $this->line("  php artisan apifootball:manage_leagues --enable=39");
        $this->line("  php artisan apifootball:manage_leagues --disable=140");
        $this->newLine();
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
                    Log::warning("API-Football rate limit (leagues). Retry after {$retryAfter}s");
                    sleep($retryAfter);
                    $retryCount++;
                    continue;
                }

                if ($response->failed()) {
                    $this->error("API retornou status {$response->status()}");
                    Log::error("API-Football leagues error: {$response->status()} - {$response->body()}");
                    return null;
                }

                return $response;

            } catch (\Exception $e) {
                $retryCount++;
                $this->error("Erro na requisicao: {$e->getMessage()}");
                Log::error("API-Football leagues request error: {$e->getMessage()}");

                if ($retryCount < $maxRetries) {
                    sleep(2 * $retryCount);
                }
            }
        }

        return null;
    }
}
