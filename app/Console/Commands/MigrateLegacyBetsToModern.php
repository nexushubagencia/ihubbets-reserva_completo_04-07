<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Aposta;
use App\Models\Bet;
use App\Services\UnifiedBetService;

class MigrateLegacyBetsToModern extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ihub:migrate-legacy-bets
                            {--status= : Filtrar por status legado (Aberto, Ganhou, Perdeu, Cancelado)}
                            {--limit=1000 : Quantidade máxima de apostas a processar}
                            {--only-missing : Processar apenas apostas sem espelho moderno}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Espelha apostas legado (apostas/palpites) na tabela moderna (bets/bet_items)';

    public function handle(UnifiedBetService $service)
    {
        $limit = (int) $this->option('limit');
        $status = $this->option('status');
        $onlyMissing = $this->option('only-missing');

        $query = Aposta::query();

        if ($status) {
            $query->where('status', $status);
        }

        if ($onlyMissing) {
            $query->whereNotIn('id', function ($q) {
                $q->select('legacy_aposta_id')
                  ->from('bets')
                  ->whereNotNull('legacy_aposta_id');
            });
        }

        $total = $query->count();
        $this->info("Apostas encontradas para processar: {$total}");

        if ($total === 0) {
            return 0;
        }

        $processed = 0;
        $errors = 0;

        $query->with('palpites')
              ->orderBy('id', 'desc')
              ->chunk(100, function ($apostas) use ($service, &$processed, &$errors) {
                  foreach ($apostas as $aposta) {
                      try {
                          $service->createFromAposta($aposta);
                          $processed++;
                          $this->info("[#{$aposta->id}] Espelhado com sucesso.");
                      } catch (\Throwable $e) {
                          $errors++;
                          $this->error("[#{$aposta->id}] Erro: {$e->getMessage()}");
                      }
                  }
              });

        $this->newLine();
        $this->info("Processamento concluído: {$processed} sucesso(s), {$errors} erro(s).");

        return $errors > 0 ? 1 : 0;
    }
}
