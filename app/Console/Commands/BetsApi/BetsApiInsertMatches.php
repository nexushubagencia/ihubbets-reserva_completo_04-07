<?php

namespace App\Console\Commands\BetsApi;

use Illuminate\Console\Command;
use App\Models\MatchModel;
use App\Models\ApifootballLeague;
use App\Models\Teams;
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

        $homeName = $event['home'] ?? $event['T1'] ?? 'Time A';
        $awayName = $event['away'] ?? $event['T2'] ?? 'Time B';
        $leagueName = $event['league'] ?? $event['LG'] ?? '';
        $country = $event['country'] ?? $event['CC'] ?? '';
        $tournament = $event['tournament'] ?? $event['TR'] ?? $leagueName;

        $homeName = TranslationService::traduzirTime($homeName);
        $awayName = TranslationService::traduzirTime($awayName);
        $leagueName = TranslationService::traduzirLiga($leagueName ?: $tournament, $country);

        $startTime = $event['time'] ?? $event['start'] ?? null;
        $dateFormatted = $startTime
            ? Carbon::parse($startTime)->format('Y-m-d H:i:s')
            : Carbon::now()->format('Y-m-d H:i:s');

        $timeUnix = $startTime ? Carbon::parse($startTime)->timestamp : now()->timestamp;

        $status = $event['status'] ?? $event['SS'] ?? '';
        $timeStatus = match($status) {
            'In Play', 'Live', '1' => '1',
            'Finished', 'FT' => '3',
            'Postponed', 'Cancelled' => '-1',
            default => '0',
        };

        $score = '';
        if (!empty($event['score']) || !empty($event['home_score'])) {
            $hs = $event['home_score'] ?? $event['SC']['home'] ?? '';
            $as = $event['away_score'] ?? $event['SC']['away'] ?? '';
            if ($hs !== '' && $as !== '') {
                $score = $hs . ' - ' . $as;
            }
        }

        $leagueId = hexdec(substr(md5($tournament ?: $leagueName), 0, 8));

        ApifootballLeague::updateOrCreate(
            ['league_id' => $leagueId, 'sport' => $sportName],
            [
                'name'    => $leagueName,
                'country' => $country ?: 'World',
                'active'  => 1,
            ]
        );

        $existing = MatchModel::where('event_id', $fi)
            ->where('site_id', $siteId)
            ->first();

        $sportMap = [
            'football' => ['sport_id' => 1, 'sport_name' => 'Futebol'],
            'basketball' => ['sport_id' => 2, 'sport_name' => 'Basquete'],
            'tennis' => ['sport_id' => 3, 'sport_name' => 'Tênis'],
            'volleyball' => ['sport_id' => 4, 'sport_name' => 'Vôlei'],
            'handball' => ['sport_id' => 5, 'sport_name' => 'Handebol'],
            'futsal' => ['sport_id' => 6, 'sport_name' => 'Futsal'],
            'ice_hockey' => ['sport_id' => 7, 'sport_name' => 'Hóquei no Gelo'],
            'baseball' => ['sport_id' => 8, 'sport_name' => 'Baseball'],
            'american_football' => ['sport_id' => 12, 'sport_name' => 'Futebol Americano'],
            'esports' => ['sport_id' => 15, 'sport_name' => 'E-Sports'],
            'boxing' => ['sport_id' => 21, 'sport_name' => 'Boxe'],
            'mma' => ['sport_id' => 22, 'sport_name' => 'MMA/UFC'],
            'cricket' => ['sport_id' => 18, 'sport_name' => 'Críquete'],
            'darts' => ['sport_id' => 17, 'sport_name' => 'Dardos'],
            'snooker' => ['sport_id' => 19, 'sport_name' => 'Sinuca'],
            'table_tennis' => ['sport_id' => 20, 'sport_name' => 'Tênis de Mesa'],
        ];

        $sportInfo = $sportMap[$sportName] ?? ['sport_id' => 0, 'sport_name' => ucfirst($sportName)];

        $data = [
            'site_id'       => $siteId,
            'event_id'      => $fi,
            'our_event_id'  => null,
            'sport_id'      => $sportInfo['sport_id'],
            'sport_name'    => $sportInfo['sport_name'],
            'league_id'     => $leagueId,
            'league_cc'     => $country,
            'league'        => $leagueName,
            'home'          => $homeName,
            'away'          => $awayName,
            'home_true'     => null,
            'away_true'     => null,
            'image_id_home' => $event['home_logo'] ?? $event['T1I'] ?? null,
            'image_id_away' => $event['away_logo'] ?? $event['T2I'] ?? null,
            'score'         => $score,
            'time_status'   => $timeStatus,
            'time'          => $timeUnix,
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
