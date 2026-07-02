<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PreBet;
use Carbon\Carbon;

class CleanupPreBets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'prebets:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Exclui pre-apostas (PINs) geradas há mais de 5 horas que não foram validadas.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // 300 minutos = 5 horas
        $limitDate = Carbon::now()->subMinutes(300);

        $deletedCount = PreBet::where('created_at', '<', $limitDate)->delete();

        $this->info("Limpeza concluída! {$deletedCount} pré-apostas expiradas foram excluídas.");
    }
}
