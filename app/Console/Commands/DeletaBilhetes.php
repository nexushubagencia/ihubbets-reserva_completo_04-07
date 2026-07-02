<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Carbon\Carbon;
use App\Models\Aposta;
use App\Models\MapaBet;
use App\Models\Odd;
use App\Models\Palpite;

class DeletaBilhetes extends Command
{

    private $dataMenor;
    protected $signature = 'command:deletaBilhetes';

    protected $description = 'Deleta os bilhetes antigos da base de dados';

    public function __construct()
    {
        parent::__construct();
        $this->dataMenor = Carbon::now()->subDays(30);
    }

    private function deletaBilhetes()
    {
        try {
            $palpites = Palpite::where('created_at', '<', $this->dataMenor)->delete();
            $odds = Odd::where('created_at', '<', $this->dataMenor)->delete();
            $mapaBets = MapaBet::where('created_at', '<', $this->dataMenor)->delete();
            $apostas = Aposta::where('created_at', '<', $this->dataMenor)->delete();

            $this->info("Bilhetes deletados com sucesso!");
            $this->line("  Palpites removidos: {$palpites}");
            $this->line("  Odds removidas: {$odds}");
            $this->line("  MapaBets removidos: {$mapaBets}");
            $this->line("  Apostas removidas: {$apostas}");

            return true;
        } catch (\Exception $e) {
            $this->error('Erro ao executar a rotina DeletaBilhetes: ' . $e->getMessage());
            return false;
        }
    }

    public function handle()
    {
        return $this->deletaBilhetes();
    }
}
