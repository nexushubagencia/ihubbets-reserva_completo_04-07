<?php

namespace App\Console\Commands\Scraper;

use Illuminate\Console\Command;
use App\Services\JogadinhaFallback;

class SyncJogadinha extends Command
{
    protected $signature = 'scraper:sync-jogadinha {--modo=today}';

    protected $description = 'Sincroniza jogos e odds do Jogadinha para o banco de dados (today|tomorrow|live)';

    public function handle(): int
    {
        $modo = $this->option('modo');

        $validModes = ['today', 'tomorrow', 'live'];
        if (!in_array($modo, $validModes)) {
            $this->error("Modo inválido: {$modo}. Use: today, tomorrow ou live.");
            return static::FAILURE;
        }

        $modoLabels = [
            'today'   => 'Hoje',
            'tomorrow'=> 'Amanhã',
            'live'    => 'Ao Vivo',
        ];

        $this->info("Iniciando sincronização Jogadinha (modo: {$modoLabels[$modo]})...");

        $result = JogadinhaFallback::syncGamesAndOdds($modo);

        if ($result) {
            $this->info("Sincronização concluída com sucesso! (modo: {$modoLabels[$modo]})");
            return static::SUCCESS;
        }

        $this->error("Falha na sincronização. Verifique os logs em storage/logs/apifootball.log");
        return static::FAILURE;
    }
}
