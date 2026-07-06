<?php

namespace App\Console\Commands\BetsApi;

use Illuminate\Console\Command;
use App\Models\MatchModel;
use App\Models\ApifootballLeague;
use App\Services\BetsApiService;
use App\Services\ApiProviderService;
use App\Services\TranslationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

class BetsApiInsertMatches extends Command
{
    protected $signature = 'betsapi:insert_matches {--sport=football} {--pages=3}';
    protected $description = 'Insere partidas da BetsAPI (Bet365) - Multi-esporte';

    private const LOCK_FILE = 'storage/logs/betsapi_insert.lock';
    private const LOG_FILE  = 'storage/logs/betsapi_insert.log';

    private int $inserted = 0;
    private int $updated  = 0;
    private int $skipped  = 0;

    public function handle(BetsApiService $betsApi, ApiProviderService $provider): int
    {
        if (!$betsApi->isConfigured()) {
            $this->error("Token BetsAPI não configurado. Configure em services.bets_api.token");
            return self::FAILURE;
        }

        $sport = $this->option('sport');
        $pages = (int) $this->option('pages');

        if ($sport === 'all') {
            $sportsToSync = array_keys(BetsApiService::SPORT_IDS);
        } else {
            if (!isset(BetsApiService::SPORT_IDS[$sport])) {
                $this->error("Esporte inválido: {$sport}. Opções: " . implode(', ', array_keys(BetsApiService::SPORT_IDS)) . ", all");
                return self::FAILURE;
            }
            $sportsToSync = [$sport];
        }

        if (!$this->acquireLock()) {
            $this->error("Outro processo está em execução.");
            return self::FAILURE;
        }

        $this->rotateLog();
        $this->cleanOldMatches();

        $siteId = config('tenant.site_id', 1);

        foreach ($sportsToSync as $sportName) {
            $sportId = BetsApiService::SPORT_IDS[$sportName];
            $sportLabel = BetsApiService::SPORT_NAMES[$sportId] ?? $sportName;

            $this->info("=== {$sportLabel} (ID: {$sportId}) ===");

            for ($page = 1; $page <= $pages; $page++) {
                $this->info("  Página {$page}/{$pages}...");
                $this->fetchAndInsert($betsApi, $siteId, $sportId, $sportName, $page);

                if ($page < $pages) {
                    sleep(2);
                }
            }
        }

        $this->releaseLock();

        $totalRequests = $betsApi->getRequestCount();
        $this->info("Concluído! Inseridos: {$this->inserted} | Atualizados: {$this->updated} | Pulados: {$this->skipped} | Requests API: {$totalRequests}");
        Log::info("betsapi:insert_matches finalizado. Inserted: {$this->inserted}, Updated: {$this->updated}, Skipped: {$this->skipped}");

        return self::SUCCESS;
    }

    private function fetchAndInsert(BetsApiService $betsApi, int $siteId, int $sportId, string $sportName, int $page): void
    {
        $response = $betsApi->getUpcomingBySport($sportId, $page);

        if (!$response || empty($response['results'])) {
            $this->warn("  Nenhum jogo encontrado na página {$page}.");
            return;
        }

        $events = $response['results'];
        $this->info("  " . count($events) . " eventos encontrados.");

        foreach ($events as $event) {
            $this->processEvent($event, $siteId, $sportId, $sportName);
        }
    }

    private function processEvent(array $event, int $siteId, int $sportId, string $sportName): void
    {
        $fi = $event['FI'] ?? $event['id'] ?? 0;
        if ($fi <= 0) {
            $this->skipped++;
            return;
        }

        $homeRaw = $event['home'] ?? null;
        $awayRaw = $event['away'] ?? null;
        $leagueRaw = $event['league'] ?? null;

        // BetsAPI /v1/bet365/upcoming retorna home/away/league como objetos {id, name}
        $homeName = is_array($homeRaw) ? ($homeRaw['name'] ?? 'Time A') : ($homeRaw ?? 'Time A');
        $awayName = is_array($awayRaw) ? ($awayRaw['name'] ?? 'Time B') : ($awayRaw ?? 'Time B');
        $leagueName = is_array($leagueRaw) ? ($leagueRaw['name'] ?? '') : ($leagueRaw ?? '');
        $tournament = $event['tournament'] ?? $event['TR'] ?? $leagueName;

        $homeId = is_array($homeRaw) ? ($homeRaw['id'] ?? null) : null;
        $awayId = is_array($awayRaw) ? ($awayRaw['id'] ?? null) : null;
        $leagueId = is_array($leagueRaw) ? ($leagueRaw['id'] ?? null) : null;

        // BetsAPI nao envia country code no bet365/upcoming, inferir pelo nome
        $country = '';
        if (!empty($event['cc'])) {
            $country = strtolower((string)$event['cc']);
        } elseif (!empty($event['CC'])) {
            $country = strtolower((string)$event['CC']);
        }

        if (empty($country)) {
            $country = TranslationService::inferirCcDaLiga($leagueName ?: $tournament);
        }

        // Traduz nomes
        $homeName = TranslationService::traduzirTime((string)$homeName);
        $awayName = TranslationService::traduzirTime((string)$awayName);

        $countryNameForTranslation = TranslationService::traduzirLeagueCc($country);
        $leagueName = TranslationService::traduzirLiga((string)$leagueName ?: (string)$tournament, $countryNameForTranslation);

        // Data/hora - BetsAPI envia Unix timestamp UTC
        $startTime = $event['time'] ?? $event['start'] ?? null;
        if ($startTime && is_numeric($startTime)) {
            $carbonDate = Carbon::createFromTimestamp((int)$startTime)->setTimezone(config('app.timezone', 'America/Fortaleza'));
        } elseif ($startTime) {
            $carbonDate = Carbon::parse($startTime);
        } else {
            $carbonDate = Carbon::now();
        }

        $dateFormatted = $carbonDate->format('Y-m-d H:i:s');

        // Status: pre-jogo padrao 0
        $timeStatus = 0;
        $status = $event['status'] ?? $event['time_status'] ?? '';
        if (in_array($status, ['In Play', 'Live', '1', 1], true)) {
            $timeStatus = 1;
        } elseif (in_array($status, ['Finished', 'FT', '3', 3], true)) {
            $timeStatus = 3;
        } elseif (in_array($status, ['Postponed', 'Cancelled', '-1', -1], true)) {
            $timeStatus = -1;
        }

        // Placar (normalmente vazio para pre-jogo)
        $score = '';
        if (!empty($event['ss']) && is_string($event['ss'])) {
            $score = $event['ss'];
        } elseif (!empty($event['score'])) {
            $score = (string)$event['score'];
        }

        // league_id: usa o ID real da BetsAPI quando disponivel, senao gera um hash estavel
        $leagueIdNumeric = $leagueId ? (int)$leagueId : (abs(crc32((string)$tournament ?: (string)$leagueName)) % 2147483647);

        // URLs dos logos usando a CDN da BetsAPI (fallback para id do time)
        $homeLogoUrl = $homeId ? "https://assets.b365api.com/images/team/m/{$homeId}.png" : null;
        $awayLogoUrl = $awayId ? "https://assets.b365api.com/images/team/m/{$awayId}.png" : null;
        $leagueLogoUrl = $leagueId ? "https://assets.b365api.com/images/league/m/{$leagueId}.png" : null;

        // Mantem tabela de ligas sincronizada
        ApifootballLeague::updateOrCreate(
            ['league_id' => $leagueIdNumeric, 'sport' => $sportName, 'site_id' => $siteId],
            [
                'name'    => $leagueName,
                'country' => $country ?: 'World',
                'logo'    => $leagueLogoUrl,
                'active'  => 1,
            ]
        );

        $existing = MatchModel::where('event_id', $fi)
            ->where('site_id', $siteId)
            ->first();

        $sportInfo = [
            'sport_id'   => $sportId,
            'sport_name' => BetsApiService::SPORT_NAMES[$sportId] ?? ucfirst($sportName),
        ];

        $data = [
            'site_id'       => $siteId,
            'event_id'      => $fi,
            'our_event_id'  => $event['our_event_id'] ?? null,
            'sport_id'      => $sportInfo['sport_id'],
            'sport_name'    => $sportInfo['sport_name'],
            'league_id'     => $leagueIdNumeric,
            'league_cc'     => $country,
            'league'        => $leagueName,
            'home'          => $homeName,
            'away'          => $awayName,
            'home_true'     => null,
            'away_true'     => null,
            'image_id_home' => $homeLogoUrl,
            'image_id_away' => $awayLogoUrl,
            'score'         => $score,
            'time_status'   => $timeStatus,
            'time'          => 0, // pre-jogo: 0; ao vivo: atualizado pelo BetsApiLiveStatus
            'date'          => $dateFormatted,
            'confronto'     => $dateFormatted . $leagueName . $homeName . $awayName,
            'visible'       => 'Sim',
            'order'         => 0,
        ];

        if ($existing) {
            // Nao sobrescreve dados ao vivo se o jogo ja comecou
            if ($existing->time_status == 1) {
                unset($data['score'], $data['time'], $data['time_status'], $data['home_true'], $data['away_true']);
            }
            $existing->update($data);
            $this->updated++;
        } else {
            MatchModel::create($data);
            $this->inserted++;
        }
    }

    private function cleanOldMatches(): void
    {
        $siteId = config('tenant.site_id', 1);
        $dateLimit = Carbon::today()->subDays(2)->format('Y-m-d') . ' 23:59:59';

        $oldMatchIds = MatchModel::where('date', '<=', $dateLimit)
            ->where('site_id', $siteId)
            ->pluck('id')
            ->toArray();

        if (empty($oldMatchIds)) return;

        $hasOpenPalpites = \App\Models\Palpite::whereIn('match_id', $oldMatchIds)
            ->where('status', 'Aberto')->exists();

        if ($hasOpenPalpites) {
            $this->warn("AVISO: Palpites abertos referenciam partidas antigas. Pulando limpeza.");
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
        if (File::exists($lockPath)) File::delete($lockPath);
    }

    private function rotateLog(): void
    {
        $logPath = base_path(self::LOG_FILE);
        if (File::exists($logPath) && File::size($logPath) > 1048576) {
            File::copy($logPath, $logPath . '.' . date('Y-m-d_His') . '.bak');
            File::put($logPath, '');
        }
    }
}
