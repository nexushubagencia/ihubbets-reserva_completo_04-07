<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SetupAllSports extends Command
{
    protected $signature = 'apifootball:setup-all-sports
                            {--dry-run : Apenas mostra o que sera feito, sem executar}';
    protected $description = 'Ativa ligas populares de todos os esportes e insere partidas';

    private const POPULAR_LEAGUES = [
        'football' => [
            // Brasil
            71 => 'Serie A - Brazil',
            72 => 'Serie B - Brazil', 
            13 => 'World Cup',
            2 => 'Champions League',
            3 => 'Europa League',
            9 => 'Copa do Brasil',
            94 => 'Paulista A1',
            95 => 'Carioca',
            // Europa
            39 => 'Premier League',
            140 => 'La Liga',
            135 => 'Serie A',
            78 => 'Bundesliga',
            61 => 'Ligue 1',
            82 => 'Primeira Liga',
            // Mundo
            34 => 'Eredivisie',
            41 => 'Championship',
        ],
        'basketball' => [
            12 => 'NBA',
            116 => 'Euroleague',
            117 => 'Eurocup',
            129 => 'NBB - Brazil',
            167 => 'Liga ACB',
        ],
        'volleyball' => [
            93 => 'Superliga - Brazil',
            97 => 'Italian Serie A1',
            98 => 'Polish PlusLiga',
            100 => 'Russian Superleague',
        ],
        'mma' => [
            67 => 'UFC',
            68 => 'Bellator',
        ],
    ];

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $siteId = config('tenant.site_id', 1);

        $this->info("=== SETUP COMPLETO DE TODOS OS ESPORTES ===");
        if ($dryRun) {
            $this->warn("MODO DRY-RUN: Apenas exibindo, sem alterar nada.");
        }
        $this->newLine();

        foreach (self::POPULAR_LEAGUES as $sport => $leagues) {
            $this->info("--- {$sport} ---");

            foreach ($leagues as $leagueId => $leagueName) {
                $this->line("  {$leagueId}: {$leagueName}");

                if (!$dryRun) {
                    $this->call('apifootball:manage_leagues', [
                        '--enable' => $leagueId,
                    ]);
                }
            }

            $this->newLine();

            if (!$dryRun) {
                $this->info("Inserindo partidas de {$sport}...");
                $this->call('apifootball:insert_matches', [
                    '--sport' => $sport,
                ]);
            }
        }

        if (!$dryRun) {
            $this->newLine();
            $this->info("Atualizando odds...");
            $this->call('apifootball:update_odds', ['--force' => true]);
        }

        $this->newLine();
        $this->info("Setup concluido!");
        $this->warn("Para ver os outros esportes no painel, va em Admin > Configuracoes e ative:");
        $this->line("  - op_basquete = Sim");
        $this->line("  - op_volei = Sim");
        $this->line("  - op_ufcbox = Sim");

        return self::SUCCESS;
    }
}