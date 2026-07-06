<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        // BetsAPI (provedor PRINCIPAL e UNICO)
        Commands\BetsApi\BetsApiInsertMatches::class,
        Commands\BetsApi\BetsApiUpdateOdds::class,
        Commands\BetsApi\BetsApiLiveStatus::class,
        Commands\BetsApi\BetsApiLiveOdds::class,
        Commands\BetsApi\BetsApiCacheLogos::class,

        // Cache - versoes modernas (USE ESTAS)
        Commands\Cache\LiveHoje::class,
        Commands\Cache\LiveAmanha::class,
        Commands\Cache\Live::class,
        Commands\Cache\AtualizaHome::class,

        // Utilitarios
        Commands\LoadDay::class,
        Commands\Ligas::class,
        Commands\LigasMain::class,
        Commands\SyncLiveMatches::class,
        Commands\SettleApiBets::class,
        Commands\SendResultsQuina::class,
        Commands\SendResultsSena::class,
        Commands\LiveScoreMultiSport::class,

        // Logos
        Commands\DownloadFlags::class,
    ];

    protected function schedule(Schedule $schedule): void
    {
        // === BetsAPI (PROVEDOR PRINCIPAL e UNICO) ===
        // Atualiza partidas de todos os esportes periodicamente
        $schedule->command('betsapi:insert_matches --sport=all --pages=5')->everySixHours()->withoutOverlapping();

        // Atualiza status ao vivo a cada 2 minutos (remove jogos iniciados do pre-jogo)
        $schedule->command('betsapi:live_status')->everyTwoMinutes()->withoutOverlapping();

        // Atualiza odds pre-jogo a cada 15 minutos (futebol principal)
        $schedule->command('betsapi:update_odds --sport=football')->everyFifteenMinutes()->withoutOverlapping();

        // Cache de logos (TheSportsDB fallback) - 2x ao dia
        $schedule->command('betsapi:cache_logos')->twiceDaily(6, 18)->withoutOverlapping();

        // === Liquidacao de apostas ===
        $schedule->command('ihub:settle-api-bets')->everyFiveMinutes()->withoutOverlapping();

        // === Cache - broadcast de dados para o frontend ===
        $schedule->command('command:liveHoje')->everyFiveMinutes()->withoutOverlapping();
        $schedule->command('command:liveAmanha')->everyFiveMinutes()->withoutOverlapping();
        $schedule->command('command:atualizaHome')->everyFiveMinutes()->withoutOverlapping();

        // === Ligas ===
        $schedule->command('command:loadLigas')->everyTenMinutes()->withoutOverlapping();
        $schedule->command('command:loadLigasMain')->everyTenMinutes()->withoutOverlapping();
        $schedule->command('command:loadDay')->dailyAt('00:01');

        // === Loto - resultados diarios as 19:30 ===
        $schedule->command('command:sendResultsQuina')->dailyAt('19:30')->withoutOverlapping();
        $schedule->command('command:sendResultSena')->dailyAt('19:30')->withoutOverlapping();
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}