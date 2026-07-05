<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        // BetsAPI (provedor PRINCIPAL)
        Commands\BetsApi\BetsApiInsertMatches::class,
        Commands\BetsApi\BetsApiUpdateOdds::class,

        // API-Football (backup/reserva)
        Commands\ApiFootball\InsertMatches::class,
        Commands\ApiFootball\UpdateOdds::class,
        Commands\ApiFootball\LiveOdds::class,
        Commands\ApiFootball\SettleBets::class,
        Commands\ApiFootball\ManageLeagues::class,
        Commands\ApiFootball\InsertMatchesFootballData::class,

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

        // Playfiver Casino
        Commands\SyncPlayfiverGames::class,
    ];

    protected function schedule(Schedule $schedule): void
    {
        // === BetsAPI (PROVEDOR PRINCIPAL) ===
        $schedule->command('betsapi:insert_matches --sport=football --pages=3')->everySixHours()->withoutOverlapping();
        $schedule->command('betsapi:insert_matches --sport=basketball --pages=2')->daily()->withoutOverlapping();
        $schedule->command('betsapi:insert_matches --sport=tennis --pages=2')->daily()->withoutOverlapping();
        $schedule->command('betsapi:insert_matches --sport=volleyball --pages=1')->daily()->withoutOverlapping();
        $schedule->command('betsapi:insert_matches --sport=mma --pages=1')->daily()->withoutOverlapping();

        $schedule->command('betsapi:update_odds --sport=football')->everyFifteenMinutes()->withoutOverlapping();
        $schedule->command('betsapi:update_odds --sport=basketball --live=1')->everyFiveMinutes()->withoutOverlapping();
        $schedule->command('betsapi:update_odds --sport=football --live=1')->everyMinute()->withoutOverlapping();

        // === API-Football (BACKUP - desabilitado se BetsAPI ativo) ===
        // $schedule->command('apifootball:live')->everyMinute()->withoutOverlapping();

        $schedule->command('ihub:settle-api-bets')->everyFiveMinutes()->withoutOverlapping();

        // === Cache - broadcast de dados para o frontend ===
        $schedule->command('command:liveHoje')->everyFiveMinutes()->withoutOverlapping();
        $schedule->command('command:liveAmanha')->everyFiveMinutes()->withoutOverlapping();

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