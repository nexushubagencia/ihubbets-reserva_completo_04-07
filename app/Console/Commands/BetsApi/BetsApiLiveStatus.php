<?php

namespace App\Console\Commands\BetsApi;

use Illuminate\Console\Command;
use App\Models\MatchModel;
use App\Models\ApifootballLeague;
use App\Services\BetsApiService;
use App\Services\TranslationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class BetsApiLiveStatus extends Command
{
    protected $signature = 'betsapi:live_status {--sport=}';
    protected $description = 'Atualiza status ao vivo das partidas da BetsAPI (inicia, finaliza, atualiza placar)';

    private int $updated = 0;
    private int $finished = 0;
    private int $started = 0;
    private int $inserted = 0;

    public function handle(BetsApiService $betsApi): int
    {
        if (!$betsApi->isConfigured()) {
            $this->error("Token BetsAPI não configurado.");
            return self::FAILURE;
        }

        $siteId = config('tenant.site_id', 1);
        $sportFilter = $this->option('sport');

        $sportsToSync = [];
        if ($sportFilter) {
            if (!isset(BetsApiService::SPORT_IDS[$sportFilter])) {
                $this->error("Esporte inválido: {$sportFilter}");
                return self::FAILURE;
            }
            $sportsToSync = [$sportFilter];
        } else {
            $sportsToSync = array_keys(BetsApiService::SPORT_IDS);
        }

        $this->info("=== Atualizando status ao vivo ===");

        foreach ($sportsToSync as $sportName) {
            $sportId = BetsApiService::SPORT_IDS[$sportName];
            $this->syncSport($betsApi, $siteId, $sportId, $sportName);
        }

        // Marca partidas que ja iniciaram (tempo passou ha mais de 6h)
        // mas nao estao ao vivo como encerradas
        $this->markPassedMatches($siteId);

        $this->info("Concluído! Inseridas: {$this->inserted} | Iniciadas: {$this->started} | Atualizadas: {$this->updated} | Finalizadas: {$this->finished}");
        Log::info("betsapi:live_status finalizado. Inserted: {$this->inserted}, Started: {$this->started}, Updated: {$this->updated}, Finished: {$this->finished}");

        return self::SUCCESS;
    }

    private function syncSport(BetsApiService $betsApi, int $siteId, int $sportId, string $sportName): void
    {
        $response = $betsApi->getInPlayFilterBySport($sportId);

        if (!$response || empty($response['results'])) {
            $this->warn("  {$sportName}: nenhum jogo ao vivo.");
            return;
        }

        $events = $response['results'];
        $this->info("  {$sportName}: " . count($events) . " jogos ao vivo.");

        foreach ($events as $event) {
            $this->processLiveEvent($event, $siteId, $sportId, $sportName);
        }
    }

    private function processLiveEvent(array $event, int $siteId, int $sportId, string $sportName): void
    {
        $fi = (int)($event['id'] ?? $event['FI'] ?? 0);
        if ($fi <= 0) return;

        $homeRaw = $event['home'] ?? null;
        $awayRaw = $event['away'] ?? null;
        $leagueRaw = $event['league'] ?? null;

        $homeName = is_array($homeRaw) ? ($homeRaw['name'] ?? 'Time A') : ($homeRaw ?? 'Time A');
        $awayName = is_array($awayRaw) ? ($awayRaw['name'] ?? 'Time B') : ($awayRaw ?? 'Time B');
        $leagueName = is_array($leagueRaw) ? ($leagueRaw['name'] ?? '') : ($leagueRaw ?? '');
        $tournament = $event['tournament'] ?? $leagueName;

        $homeId = is_array($homeRaw) ? ($homeRaw['id'] ?? null) : null;
        $awayId = is_array($awayRaw) ? ($awayRaw['id'] ?? null) : null;
        $leagueId = is_array($leagueRaw) ? ($leagueRaw['id'] ?? null) : null;

        // Traduz nomes
        $homeName = TranslationService::traduzirTime((string)$homeName);
        $awayName = TranslationService::traduzirTime((string)$awayName);

        // Infere country code pelo nome da liga
        $country = TranslationService::inferirCcDaLiga($leagueName ?: $tournament);
        $countryNameForTranslation = TranslationService::traduzirLeagueCc($country);
        $leagueName = TranslationService::traduzirLiga((string)$leagueName ?: (string)$tournament, $countryNameForTranslation);

        // Data/hora de inicio
        $startTime = $event['time'] ?? null;
        if ($startTime && is_numeric($startTime)) {
            $startCarbon = Carbon::createFromTimestamp((int)$startTime)->setTimezone(config('app.timezone', 'America/Fortaleza'));
        } else {
            $startCarbon = Carbon::now();
        }

        // Minutos decorridos (para o frontend)
        $elapsedMinutes = (int) round($startCarbon->copy()->setTimezone('UTC')->diffInMinutes(Carbon::now('UTC')));
        if ($elapsedMinutes < 0) $elapsedMinutes = 0;

        $score = $event['ss'] ?? '';
        $homeTrue = null;
        $awayTrue = null;
        if ($score && str_contains($score, '-')) {
            [$homeTrue, $awayTrue] = array_map('trim', explode('-', $score));
        }

        $leagueIdNumeric = $leagueId ? (int)$leagueId : (abs(crc32((string)$tournament ?: (string)$leagueName)) % 2147483647);

        // Logos
        $homeLogoUrl = $homeId ? "https://assets.b365api.com/images/team/m/{$homeId}.png" : null;
        $awayLogoUrl = $awayId ? "https://assets.b365api.com/images/team/m/{$awayId}.png" : null;
        $leagueLogoUrl = $leagueId ? "https://assets.b365api.com/images/league/m/{$leagueId}.png" : null;

        // Atualiza tabela de ligas
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

        $data = [
            'site_id'       => $siteId,
            'event_id'      => $fi,
            'our_event_id'  => $event['our_event_id'] ?? null,
            'sport_id'      => $sportId,
            'sport_name'    => BetsApiService::SPORT_NAMES[$sportId] ?? ucfirst($sportName),
            'league_id'     => $leagueIdNumeric,
            'league_cc'     => $country,
            'league'        => $leagueName,
            'home'          => $homeName,
            'away'          => $awayName,
            'home_true'     => $homeTrue,
            'away_true'     => $awayTrue,
            'image_id_home' => $homeLogoUrl,
            'image_id_away' => $awayLogoUrl,
            'score'         => $score,
            'time_status'   => 1,
            'time'          => $elapsedMinutes,
            'date'          => $startCarbon->format('Y-m-d H:i:s'),
            'confronto'     => $startCarbon->format('Y-m-d H:i:s') . $leagueName . $homeName . $awayName,
            'visible'       => 'Sim',
            'live_status'   => 'Live',
        ];

        if ($existing) {
            $wasPreMatch = $existing->time_status == 0;
            $existing->update($data);
            $this->updated++;
            if ($wasPreMatch) {
                $this->started++;
            }
        } else {
            MatchModel::create($data);
            $this->inserted++;
        }
    }

    /**
     * Marca partidas que ja deveriam ter comecado (tempo passou ha mais de 6h)
     * mas nao estao ao vivo como finalizadas.
     */
    private function markPassedMatches(int $siteId): void
    {
        $cutoff = Carbon::now('UTC')->subHours(6);

        $matches = MatchModel::where('site_id', $siteId)
            ->where('time_status', 0)
            ->where('date', '<', $cutoff->format('Y-m-d H:i:s'))
            ->get();

        foreach ($matches as $match) {
            $match->update([
                'time_status' => 3,
                'visible'     => 'Não',
                'score'       => $match->score ?: '0 - 0',
            ]);
            $this->finished++;
        }
    }
}
