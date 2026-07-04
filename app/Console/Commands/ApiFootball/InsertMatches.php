<?php

namespace App\Console\Commands\ApiFootball;

use Illuminate\Console\Command;
use App\Models\MatchModel;
use App\Models\ApifootballLeague;
use App\Models\Teams;
use App\Services\ApiProviderService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

class InsertMatches extends Command
{
    protected $signature = 'apifootball:insert_matches {--sport=football}';
    protected $description = 'Insere partidas da API-Football (api-sports.io) para o IHUB';

    private const LOCK_FILE = 'storage/logs/apifootball_insert.lock';
    private const LOG_FILE  = 'storage/logs/apifootball_insert.log';

    private const SPORT_MAP = [
        'football'   => ['sport_id' => 1, 'sport_name' => 'Futebol'],
        'basketball' => ['sport_id' => 2, 'sport_name' => 'Basquete'],
        'volleyball' => ['sport_id' => 3, 'sport_name' => 'Volei'],
        'mma'        => ['sport_id' => 4, 'sport_name' => 'MMA/UFC'],
    ];

    private int $inserted = 0;
    private int $updated  = 0;

    public function handle(ApiProviderService $provider): int
    {
        if (!$provider->isApiFootball()) {
            $this->warn("Provedor ativo: {$provider->getProviderLabel()}. ");
            $this->warn("Use 'api:provider api-football' para ativar a API-Football.");
            $this->warn("Ou use os comandos BetsAPI diretamente (command:insert-match-futebol, etc).");
            return self::SUCCESS;
        }

        $sport = $this->option('sport');

        if (!isset(self::SPORT_MAP[$sport])) {
            $this->error("Esporte invalido: {$sport}. Opcoes: " . implode(', ', array_keys(self::SPORT_MAP)));
            return self::FAILURE;
        }

        if (!$this->acquireLock()) {
            $this->error("Outro processo esta em execucao. Abortando.");
            return self::FAILURE;
        }

        $this->rotateLog();
        $this->cleanOldMatches();

        $apiKey  = config('services.apifootball.api_key');
        $apiUrl  = config('services.apifootball.api_url');
        $siteId  = config('tenant.site_id', 1);

        if (empty($apiKey)) {
            $this->error("API key nao configurada em services.apifootball.api_key");
            $this->releaseLock();
            return self::FAILURE;
        }

        $sportConfig = self::SPORT_MAP[$sport];

        $activeLeagues = ApifootballLeague::where('sport', $sport)
            ->where('active', true)
            ->where('site_id', $siteId)
            ->pluck('league_id')
            ->toArray();

        if (empty($activeLeagues)) {
            $this->warn("Nenhuma liga ativa encontrada para {$sport}. Use apifootball:manage_leagues para ativar ligas.");
            $this->releaseLock();
            return self::SUCCESS;
        }

        $this->info("Esporte: {$sportConfig['sport_name']} | Ligas ativas: " . count($activeLeagues));

        $dates = [];
        for ($i = 0; $i < 5; $i++) {
            $dates[] = Carbon::today()->addDays($i)->format('Y-m-d');
        }
        $this->info("Buscando partidas para 5 dias: " . implode(', ', $dates));

        foreach ($dates as $date) {
            $this->info("Buscando partidas para {$date}...");
            $this->fetchAndInsert($apiKey, $apiUrl, $siteId, $sportConfig, $activeLeagues, $date);
        }

        $this->releaseLock();

        $this->info("Concluido! Inseridas: {$this->inserted} | Atualizadas: {$this->updated}");
        Log::info("apifootball:insert_matches [{$sport}] finalizado. Inseridas: {$this->inserted}, Atualizadas: {$this->updated}");

        return self::SUCCESS;
    }

    private function fetchAndInsert(
        string $apiKey,
        string $apiUrl,
        int    $siteId,
        array  $sportConfig,
        array  $activeLeagues,
        string $date
    ): void {
        // Usar URL especifica do esporte
        $sport = $this->option('sport');
        $sportUrls = config('services.apifootball.urls', []);
        $sportApiUrl = $sportUrls[$sport] ?? $apiUrl;
        $url = $sportApiUrl . '/fixtures';

        $response = $this->makeRequest($apiKey, $url, [
            'date' => $date,
        ]);

        if ($response === null) {
            $this->error("Falha ao buscar dados da API para {$date}.");
            return;
        }

        $body = $response->json();
        $fixtures = $body['response'] ?? [];
        $results  = $body['results'] ?? 0;

        $this->info("  {$date}: {$results} fixtures encontrados na API.");

        if (empty($fixtures)) {
            return;
        }

        $matched = 0;
        foreach ($fixtures as $fixture) {
            $leagueId = $fixture['league']['id'] ?? 0;

            if (!in_array($leagueId, $activeLeagues)) {
                continue;
            }

            $matched++;
            $this->processFixture($fixture, $siteId, $sportConfig, $apiKey, $apiUrl);
        }

        $this->info("  {$date}: {$matched} fixtures correspondem a ligas ativas.");
    }

    private function processFixture(
        array  $fixture,
        int    $siteId,
        array  $sportConfig,
        string $apiKey,
        string $apiUrl
    ): void {
        $fixtureId  = $fixture['fixture']['id'] ?? 0;
        $status     = $fixture['fixture']['status']['short'] ?? '';
        $timestamp  = $fixture['fixture']['timestamp'] ?? 0;
        $dateRaw    = $fixture['fixture']['date'] ?? '';

        $homeName   = $fixture['teams']['home']['name'] ?? 'Time A';
        $awayName   = $fixture['teams']['away']['name'] ?? 'Time B';
        $homeLogo   = $fixture['teams']['home']['logo'] ?? null;
        $awayLogo   = $fixture['teams']['away']['logo'] ?? null;
        $homeId     = $fixture['teams']['home']['id'] ?? 0;
        $awayId     = $fixture['teams']['away']['id'] ?? 0;

        $leagueId   = $fixture['league']['id'] ?? 0;
        $leagueName = $fixture['league']['name'] ?? '';
        $leagueCC   = $fixture['league']['country'] ?? '';

        $homeGoals  = $fixture['goals']['home'] ?? 0;
        $awayGoals  = $fixture['goals']['away'] ?? 0;
        $score      = ($homeGoals !== null && $awayGoals !== null)
            ? $homeGoals . ' - ' . $awayGoals
            : '';

        $timeStatus = $this->mapApiStatus($status);

        $dateObj = $timestamp > 0
            ? Carbon::createFromTimestamp($timestamp, 'America/Sao_Paulo')
            : Carbon::parse($dateRaw, 'America/Sao_Paulo');

        $dateFormatted = $dateObj->format('Y-m-d H:i:s');

        $this->syncTeam($homeId, $homeName, $homeLogo, $siteId);
        $this->syncTeam($awayId, $awayName, $awayLogo, $siteId);

        $existing = MatchModel::where('event_id', $fixtureId)
            ->where('site_id', $siteId)
            ->first();

        $data = [
            'site_id'       => $siteId,
            'event_id'      => $fixtureId,
            'our_event_id'  => null,
            'sport_id'      => $sportConfig['sport_id'],
            'sport_name'    => $sportConfig['sport_name'],
            'league_id'     => $leagueId,
            'league_cc'     => $leagueCC,
            'league'        => $leagueName,
            'home'          => $homeName,
            'away'          => $awayName,
            'home_true'     => null,
            'away_true'     => null,
            'image_id_home' => $homeLogo,
            'image_id_away' => $awayLogo,
            'score'         => $score,
            'time_status'   => $timeStatus,
            'time'          => $timestamp,
            'date'          => $dateFormatted,
            'confronto'     => $dateFormatted . $leagueName . $homeName . $awayName,
            'visible'       => 'Sim',
            'order'         => 0,
        ];

        if ($existing) {
            $existing->update($data);
            $this->updated++;
        } else {
            MatchModel::create($data);
            $this->inserted++;
        }
    }

    private function syncTeam(int $teamId, string $name, ?string $logo, int $siteId): void
    {
        if ($teamId <= 0) return;

        $team = Teams::where('team_id', $teamId)->where('site_id', $siteId)->first();

        if ($team) {
            $team->update(['name' => $name, 'logo' => $logo]);
        } else {
            Teams::create([
                'team_id' => $teamId,
                'name'    => $name,
                'logo'    => $logo,
                'site_id' => $siteId,
            ]);
        }
    }

    private function mapApiStatus(string $status): string
    {
        $map = [
            'TBD'  => '0',
            'NS'   => '0',
            '1H'   => '1',
            'HT'   => '1',
            '2H'   => '1',
            'ET'   => '1',
            'P'    => '1',
            'BT'   => '1',
            'FT'   => '3',
            'PST'  => '-1',
            'CANC' => '-1',
            'ABD'  => '-1',
            'AWD'  => '-1',
            'WO'   => '-1',
            'LIVE' => '1',
        ];

        return $map[$status] ?? '0';
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
                    Log::warning("API-Football rate limit. Retry after {$retryAfter}s");
                    sleep($retryAfter);
                    $retryCount++;
                    continue;
                }

                if ($response->failed()) {
                    $this->error("API retornou status {$response->status()}");
                    Log::error("API-Football error: {$response->status()} - {$response->body()}");
                    return null;
                }

                $this->logRequest($url, $params, $response->json());
                $this->incrementRequestCount();
                return $response;

            } catch (\Exception $e) {
                $retryCount++;
                $this->error("Erro na requisicao: {$e->getMessage()}");
                Log::error("API-Football request error: {$e->getMessage()}");

                if ($retryCount < $maxRetries) {
                    sleep(2 * $retryCount);
                }
            }
        }

        return null;
    }

    private function cleanOldMatches(): void
    {
        $siteId = config('tenant.site_id', 1);
        $dateLimit = Carbon::today()->subDays(2)->format('Y-m-d') . ' 23:59:59';

        // Verificar se há palpites abertos referenciando partidas antigas
        $oldMatchIds = MatchModel::where('date', '<=', $dateLimit)
            ->where('site_id', $siteId)
            ->pluck('id')
            ->toArray();

        if (empty($oldMatchIds)) {
            return;
        }

        $hasOpenPalpites = \App\Models\Palpite::whereIn('match_id', $oldMatchIds)
            ->where('status', 'Aberto')
            ->exists();

        if ($hasOpenPalpites) {
            $this->warn("AVISO: Existem palpites abertos referenciando partidas antigas. Pulando limpeza.");
            return;
        }

        $deleted = MatchModel::whereIn('id', $oldMatchIds)->delete();

        if ($deleted > 0) {
            $this->info("Removidas {$deleted} partidas antigas.");
        }
    }

    private function acquireLock(): bool
    {
        $lockPath = base_path(self::LOCK_FILE);

        if (File::exists($lockPath)) {
            $lockTime = (int) File::get($lockPath);
            if (time() - $lockTime > 600) {
                File::delete($lockPath);
            } else {
                return false;
            }
        }

        File::put($lockPath, (string) time());
        return true;
    }

    private function releaseLock(): void
    {
        $lockPath = base_path(self::LOCK_FILE);
        if (File::exists($lockPath)) {
            File::delete($lockPath);
        }
    }

    private function rotateLog(): void
    {
        $logPath = base_path(self::LOG_FILE);
        if (File::exists($logPath) && File::size($logPath) > 1048576) {
            File::copy($logPath, $logPath . '.' . date('Y-m-d_His') . '.bak');
            File::put($logPath, '');
            $this->info("Log rotacionado.");
        }
    }

    private function logRequest(string $url, array $params, mixed $response): void
    {
        $entry = [
            'timestamp' => now()->toIso8601String(),
            'url'       => $url,
            'params'    => $params,
            'status'    => $response['results'] ?? 0,
            'remaining' => $response['remaining'] ?? 'N/A',
        ];

        File::append(
            base_path(self::LOG_FILE),
            json_encode($entry) . PHP_EOL
        );
    }

    /**
     * Incrementa o contador de requisicoes do dia (compartilhado com UpdateOdds)
     */
    private function incrementRequestCount(): void
    {
        \App\Services\RateLimiterService::incrementRequestCount();
    }
}
