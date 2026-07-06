<?php

namespace App\Console\Commands\BetsApi;

use Illuminate\Console\Command;
use App\Models\MatchModel;
use App\Services\BetsApiService;
use App\Services\BetsApiLogoService;
use Illuminate\Support\Facades\Log;

class BetsApiCacheLogos extends Command
{
    protected $signature = 'betsapi:cache_logos {--sport=}';
    protected $description = 'Cacheia logos de times e ligas (BetsAPI CDN + TheSportsDB fallback)';

    private int $teamLogos = 0;
    private int $leagueLogos = 0;
    private int $notFound = 0;

    public function handle(): int
    {
        $siteId = config('tenant.site_id', 1);
        $sportFilter = $this->option('sport');

        $query = MatchModel::where('site_id', $siteId)
            ->where('date', '>=', now()->subDays(1))
            ->where('date', '<=', now()->addDays(5));

        if ($sportFilter) {
            if (!isset(BetsApiService::SPORT_IDS[$sportFilter])) {
                $this->error("Esporte inválido: {$sportFilter}");
                return self::FAILURE;
            }
            $query->where('sport_id', BetsApiService::SPORT_IDS[$sportFilter]);
        }

        $matches = $query->get();

        $this->info("Cacheando logos para " . count($matches) . " partidas...");
        $bar = $this->output->createProgressBar(count($matches));
        $bar->start();

        foreach ($matches as $match) {
            $this->cacheMatchLogos($match);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        $this->info("Concluído! Logos times: {$this->teamLogos} | Logos ligas: {$this->leagueLogos} | Não encontrados: {$this->notFound}");
        Log::info("betsapi:cache_logos finalizado. Teams: {$this->teamLogos}, Leagues: {$this->leagueLogos}, NotFound: {$this->notFound}");

        return self::SUCCESS;
    }

    private function cacheMatchLogos($match): void
    {
        $sportSlug = $this->sportSlug($match->sport_name);

        // Time da casa
        $homeLogo = BetsApiLogoService::cacheTeamLogo($match->image_id_home, $match->home, $sportSlug);
        if ($homeLogo) {
            $match->update(['image_id_home' => $homeLogo]);
            $this->teamLogos++;
        }

        // Time visitante
        $awayLogo = BetsApiLogoService::cacheTeamLogo($match->image_id_away, $match->away, $sportSlug);
        if ($awayLogo) {
            $match->update(['image_id_away' => $awayLogo]);
            $this->teamLogos++;
        }

        // Liga
        $leagueLogo = BetsApiLogoService::cacheLeagueLogo(null, $match->league, $sportSlug);
        if ($leagueLogo) {
            // Salvamos o logo da liga em uma coluna temporaria? Nao temos coluna dedicada.
            // Por enquanto, nao salvamos no banco. O formatting trait resolve por nome.
        }

        if (!$homeLogo && !$awayLogo) {
            $this->notFound++;
        }
    }

    private function sportSlug(?string $sportName): string
    {
        $map = [
            'Futebol' => 'football',
            'Basquete' => 'basketball',
            'Tênis' => 'tennis',
            'Vôlei' => 'volleyball',
            'Handebol' => 'handball',
            'Futsal' => 'futsal',
            'Hóquei no Gelo' => 'ice_hockey',
            'Baseball' => 'baseball',
            'Futebol Americano' => 'american_football',
            'E-Sports' => 'esports',
            'Críquete' => 'cricket',
            'Dardos' => 'darts',
            'Sinuca' => 'snooker',
            'Tênis de Mesa' => 'table_tennis',
            'Boxe' => 'boxing',
            'MMA/UFC' => 'mma',
        ];

        return $map[$sportName] ?? 'football';
    }
}
