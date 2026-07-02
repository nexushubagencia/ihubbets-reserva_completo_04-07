<?php

namespace App\Console\Commands\ApiFootball;

use Illuminate\Console\Command;
use App\Models\MatchModel;
use App\Models\ApifootballLeague;
use App\Models\Teams;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

class InsertMatchesFootballData extends Command
{
    protected $signature = 'apifootball:insert_matches_fd';
    protected $description = 'Insere partidas da football-data.org como fallback';

    private const LOCK_FILE = 'storage/logs/footballdata_insert.lock';
    private const LOG_FILE  = 'storage/logs/footballdata_insert.log';

    private const COMPETITION_MAP = [
        'BSA' => 71,   // Campeonato Brasileiro Série A
        'BSB' => 72,   // Campeonato Brasileiro Série B
        'PL'  => 39,   // Premier League
        'PD'  => 140,  // La Liga
        'SA'  => 88,   // Serie A Italy
        'CL'  => 2,    // Champions League
        'EL'  => 3,    // Europa League
        'WC'  => 1,    // World Cup
        'FL1' => 61,   // Ligue 1
        'BL1' => 78,   // Bundesliga
        'PPL' => 94,   // Primeira Liga
        'DED' => 88,   // Eredivisie
        'ELC' => 16,   // Championship
        'CLI' => 13,   // Copa Libertadores
    ];

    private int $inserted = 0;
    private int $updated  = 0;
    private int $skipped  = 0;

    public function handle(): int
    {
        if (!$this->acquireLock()) {
            $this->error("Outro processo esta em execucao. Abortando.");
            return self::FAILURE;
        }

        $this->rotateLog();

        $apiKey = config('services.footballdata.api_key');
        $apiUrl = config('services.footballdata.api_url');
        $siteId = config('tenant.site_id', 1);

        if (empty($apiKey)) {
            $this->error("API key da football-data.org nao configurada em services.footballdata.api_key");
            $this->releaseLock();
            return self::FAILURE;
        }

        $today    = Carbon::today()->format('Y-m-d');
        $tomorrow = Carbon::tomorrow()->format('Y-m-d');

        $this->info("=== Football-Data.org - Insercao de Partidas ===");
        $this->info("Data: hoje={$today}, amanha={$tomorrow}");
        $this->info("");

        // Buscar partidas de hoje e amanha via endpoint global
        foreach ([$today, $tomorrow] as $date) {
            $this->info("--- Buscando partidas para {$date} ---");
            $this->fetchMatchesByDate($apiKey, $apiUrl, $siteId, $date);
            sleep(12); // Rate limit: 10 req/min
        }

        $this->releaseLock();

        $this->info("");
        $this->info("=== CONCLUIDO ===");
        $this->info("Inseridas: {$this->inserted} | Atualizadas: {$this->updated} | Ignoradas: {$this->skipped}");
        Log::info("apifootball:insert_matches_fd finalizado. I:{$this->inserted} U:{$this->updated} S:{$this->skipped}");

        return self::SUCCESS;
    }

    private function fetchMatchesByDate(string $apiKey, string $apiUrl, int $siteId, string $date): void
    {
        $response = $this->makeRequest($apiKey, "{$apiUrl}/matches", [
            'date' => $date,
        ]);

        if ($response === null) {
            $this->error("  Falha ao buscar partidas para {$date}");
            return;
        }

        $body    = $response->json();
        $matches = $body['matches'] ?? [];
        $count   = $body['resultSet']['count'] ?? 0;

        $this->info("  {$date}: {$count} partidas encontradas na API.");

        if (empty($matches)) {
            return;
        }

        foreach ($matches as $match) {
            $this->processMatch($match, $siteId);
        }
    }

    private function processMatch(array $match, int $siteId): void
    {
        $competitionCode = $match['competition']['code'] ?? '';

        // Verificar se a competicao esta mapeada
        if (!isset(self::COMPETITION_MAP[$competitionCode])) {
            $this->skipped++;
            return; // Competicao nao mapeada, pular
        }

        $ourLeagueId = self::COMPETITION_MAP[$competitionCode];

        // Verificar se a liga esta ativa no sistema
        $leagueExists = ApifootballLeague::where('league_id', $ourLeagueId)
            ->where('active', true)
            ->where('site_id', $siteId)
            ->exists();

        if (!$leagueExists) {
            $this->skipped++;
            return; // Liga nao ativa
        }

        $matchId    = $match['id'] ?? 0;
        $status     = $match['status'] ?? '';
        $utcDate    = $match['utcDate'] ?? '';
        $matchday   = $match['matchday'] ?? null;
        $stage      = $match['stage'] ?? '';

        $homeName   = $match['homeTeam']['name'] ?? 'Time A';
        $awayName   = $match['awayTeam']['name'] ?? 'Time B';
        $homeCrest  = $match['homeTeam']['crest'] ?? null;
        $awayCrest  = $match['awayTeam']['crest'] ?? null;
        $homeId     = $match['homeTeam']['id'] ?? 0;
        $awayId     = $match['awayTeam']['id'] ?? 0;

        $competitionName = $match['competition']['name'] ?? '';

        $score         = $this->extractScore($match);
        $timeStatus    = $this->mapStatus($status);
        $dateObj       = Carbon::parse($utcDate, 'UTC')->setTimezone('America/Sao_Paulo');
        $dateFormatted = $dateObj->format('Y-m-d H:i:s');
        $timestamp     = $dateObj->timestamp;

        // Sincronizar times
        $this->syncTeam($homeId, $homeName, $homeCrest, $siteId);
        $this->syncTeam($awayId, $awayName, $awayCrest, $siteId);

        // Usar o ID da football-data.org diretamente (integer)
        // IDs são muito maiores que api-sports.io, sem colisão
        $eventId = $matchId;

        $existing = MatchModel::where('event_id', $eventId)
            ->where('site_id', $siteId)
            ->first();

        $data = [
            'site_id'       => $siteId,
            'event_id'      => $eventId,
            'our_event_id'  => null,
            'sport_id'      => 1,
            'sport_name'    => 'Futebol',
            'league_id'     => $ourLeagueId,
            'league_cc'     => $competitionCode,
            'league'        => $competitionName,
            'home'          => $homeName,
            'away'          => $awayName,
            'home_true'     => null,
            'away_true'     => null,
            'image_id_home' => $homeCrest,
            'image_id_away' => $awayCrest,
            'score'         => $score,
            'time_status'   => $timeStatus,
            'time'          => $timestamp,
            'date'          => $dateFormatted,
            'confronto'     => $dateFormatted . $competitionName . $homeName . $awayName,
            'visible'       => 'Sim',
            'order'         => 0,
        ];

        if ($existing) {
            $existing->update($data);
            $this->updated++;
            $this->info("  [ATU] {$homeName} x {$awayName} ({$competitionCode}) - {$dateFormatted}");
        } else {
            MatchModel::create($data);
            $this->inserted++;
            $this->info("  [INS] {$homeName} x {$awayName} ({$competitionCode}) - {$dateFormatted}");
        }
    }

    private function extractScore(array $match): string
    {
        $fullTime = $match['score']['fullTime'] ?? [];
        $home     = $fullTime['home'] ?? null;
        $away     = $fullTime['away'] ?? null;

        if ($home !== null && $away !== null) {
            return "{$home} - {$away}";
        }

        return '';
    }

    private function mapStatus(string $status): string
    {
        $map = [
            'SCHEDULED'         => '0',
            'TIMED'             => '0',
            'IN_PLAY'           => '1',
            'PAUSED'            => '1',
            'EXTRA_TIME'        => '1',
            'PENALTY_SHOOTOUT'  => '1',
            'FINISHED'          => '3',
            'SUSPENDED'         => '-1',
            'POSTPONED'         => '-1',
            'CANCELLED'         => '-1',
            'AWARDED'           => '3',
        ];

        return $map[$status] ?? '0';
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

    private function makeRequest(string $apiKey, string $url, array $params = [])
    {
        $maxRetries = 3;
        $retryCount = 0;

        while ($retryCount < $maxRetries) {
            try {
                $response = Http::withHeaders([
                    'X-Auth-Token' => $apiKey,
                    'Accept'       => 'application/json',
                ])->timeout(30)->get($url, $params);

                if ($response->status() === 429) {
                    $retryAfter = (int) $response->header('X-RequestCounter-Reset', 60);
                    $this->warn("  Rate limit atingido. Aguardando {$retryAfter}s...");
                    Log::warning("football-data.org rate limit. Retry after {$retryAfter}s");
                    sleep($retryAfter);
                    $retryCount++;
                    continue;
                }

                if ($response->failed()) {
                    $this->error("  API retornou status {$response->status()}");
                    Log::error("football-data.org error: {$response->status()} - {$response->body()}");
                    return null;
                }

                $remaining = $response->header('X-RequestsAvailable', 'N/A');
                $this->info("  [API] Requests restantes: {$remaining}");

                $this->logRequest($url, $params, $response->json());
                return $response;

            } catch (\Exception $e) {
                $retryCount++;
                $this->error("  Erro na requisicao: {$e->getMessage()}");
                Log::error("football-data.org request error: {$e->getMessage()}");

                if ($retryCount < $maxRetries) {
                    sleep(2 * $retryCount);
                }
            }
        }

        return null;
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
            'count'     => $response['resultSet']['count'] ?? 0,
        ];

        File::append(
            base_path(self::LOG_FILE),
            json_encode($entry) . PHP_EOL
        );
    }
}
