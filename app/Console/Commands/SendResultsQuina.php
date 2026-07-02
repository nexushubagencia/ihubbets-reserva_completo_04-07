<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Aposta;
use App\Models\PalpiteLoto;
use App\Models\LotoResult;
use App\Models\User;
use Carbon\Carbon;

class SendResultsQuina extends Command
{
    protected $signature = 'command:sendResultsQuina';
    protected $description = 'Processa resultados da Quininha e define ganhadores/perdedores';

    public function handle()
    {
        $this->info('Processando resultados da Quininha...');

        $concurso = Carbon::yesterday()->format('d/m/Y');

        $result = LotoResult::where('concurso', $concurso)
            ->where('tipo', 'Quina')
            ->first();

        if (!$result) {
            $this->info("Nenhum resultado encontrado para o concurso {$concurso}");
            return 0;
        }

        $dezenas = $result->dezenas;

        $apostas = Aposta::where('modalidade', 'Loto')
            ->where('tipo', 'Quininha')
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

            if ($acertos >= 5) {
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

        $this->info("Quininha processada: {$ganhou} ganharam, {$perdeu} perderam");
        return 0;
    }
}
