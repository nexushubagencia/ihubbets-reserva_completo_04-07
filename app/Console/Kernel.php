<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        Commands\ApiFootball\InsertMatches::class,
        Commands\ApiFootball\UpdateOdds::class,
        Commands\ApiFootball\LiveOdds::class,
        Commands\ApiFootball\SettleBets::class,
        Commands\ApiFootball\ManageLeagues::class,
        Commands\ApiFootball\InsertMatchesFootballData::class,
        Commands\Cache\LiveHoje::class,
        Commands\Cache\LiveAmanha::class,
        Commands\Cache\Live::class,
        Commands\Cache\AtualizaHome::class,
        Commands\LiveAfterTomorow::class,
        Commands\LiveScore::class,
        Commands\LoadDay::class,
        Commands\Ligas::class,
        Commands\LigasMain::class,
        Commands\SyncLiveMatches::class,
        Commands\SettleApiBets::class,
        Commands\SendResultsQuina::class,
        Commands\SendResultsSena::class,
        Commands\LiveScoreMultiSport::class,
    ];

    protected function schedule(Schedule $schedule): void
    {
        // Live - atualiza partidas ao vivo a cada 1 minuto
        $schedule->command('apifootball:live')->everyMinute()->withoutOverlapping();

        // Scores ao vivo
        $schedule->command('command:liveScore')->everyMinute()->withoutOverlapping();

        // Broadcast de dados atualizados para o frontend
        $schedule->command('command:liveHoje')->everyFiveMinutes()->withoutOverlapping();
        $schedule->command('command:liveAmanha')->everyFiveMinutes()->withoutOverlapping();
        $schedule->command('command:liveAfter')->everyFiveMinutes()->withoutOverlapping();
        $schedule->command('command:live')->everyMinute()->withoutOverlapping();

        // Carregar ligas
        $schedule->command('command:loadLigas')->everyTenMinutes()->withoutOverlapping();
        $schedule->command('command:loadLigasMain')->everyTenMinutes()->withoutOverlapping();

        // Load day names
        $schedule->command('command:loadDay')->dailyAt('00:01');

        // Sincronizar partidas (buscar novas)
        $schedule->command('ihub:sync-matches')->hourly()->withoutOverlapping();

        // Odds - atualizar com cuidado para nao estourar quota
        $schedule->command('ihub:sync-live')->everyFiveMinutes()->withoutOverlapping();

        // Liquidar apostas
        $schedule->command('ihub:settle-api-bets')->everyFiveMinutes()->withoutOverlapping();

        // Loto - processar resultados diariamente as 19:30 (apos sorteio 19:00)
        $schedule->command('command:sendResultsQuina')->dailyAt('19:30')->withoutOverlapping();
        $schedule->command('command:sendResultSena')->dailyAt('19:30')->withoutOverlapping();

        // Multi-sport live scores (basquete e volei quando em temporada)
        $schedule->command('apifootball:live-multi --sport=basketball')->everyMinute()->withoutOverlapping();
        $schedule->command('apifootball:live-multi --sport=volleyball')->everyMinute()->withoutOverlapping();
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}