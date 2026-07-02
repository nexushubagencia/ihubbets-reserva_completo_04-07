<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Aposta;
use App\Models\PalpiteLoto;
use App\Models\LotoResult;
use App\Models\User;
use Carbon\Carbon;

class SendResultsSena extends Command
{
    protected $signature = 'command:sendResultSena';
    protected $description = 'Processa resultados da Seninha (Mega-Sena) e define ganhadores/perdedores';

    public function handle()
    {
        $this->info('Processando resultados da Seninha...');

        $concurso = Carbon::yesterday()->format('d/m/Y');

        $result = LotoResult::where('concurso', $concurso)
            ->where('tipo', 'Mega-Sena')
            ->first();

        if (!$result) {
            $this->info("Nenhum resultado encontrado para o concurso {$concurso}");
            return 0;
        }

        $dezenas = $result->dezenas;

        $apostas = Aposta::where('modalidade', 'Loto')
            ->where('tipo', 'Seninha')
            ->where('concurso', $concurso)
            ->where('status', 'Aberto')
            ->with(['palpitesLoto', 'user'])
            ->get();

        $this->info("Encontradas {$apostas->count()} apostas abertas para processar");

        $ganhou = 0;
        $perdeu = 0;

        foreach ($apostas as $aposta) {
            $palpites = $aposta->palpitesLoto->pluck('dezena')->toArray();
            $acertos = count(array_intersect($palpites, $dezenas));

            if ($acertos >= 6) {
                $aposta->update([
                    'status' => 'Ganhou',
                    'acertos_palpites' => $acertos,
                    'resultado_loto' => implode('-', $dezenas),
                ]);

                if ($aposta->user) {
                    $aposta->user->increment('saldo_loto', $aposta->retorno_possivel);
                    $aposta->user->increment('saidas', $aposta->retorno_possivel);
                }

                $ganhou++;
                $this->info("Aposta #{$aposta->id} - GANHOU ({$acertos} acertos)");
            } else {
                $aposta->update([
                    'status' => 'Perdeu',
                    'acertos_palpites' => $acertos,
                    'resultado_loto' => implode('-', $dezenas),
                ]);

                $perdeu++;
                $this->info("Aposta #{$aposta->id} - PERDEU ({$acertos} acertos)");
            }
        }

        $this->info("Seninha processada: {$ganhou} ganharam, {$perdeu} perderam");
        return 0;
    }
}
