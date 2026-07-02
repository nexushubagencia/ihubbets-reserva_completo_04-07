<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Site;
use App\Models\User;
use Carbon\Carbon;

class CheckBillingStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billing:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica faturas vencidas das bancas (Tenants) e suspende automaticamente se passar da carência';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando checagem de inadimplência nas bancas...');

        $hoje = Carbon::now();
        // Definimos uma carência padrão de 3 dias
        $carenciaDias = 3; 

        // Recuperar bancas que AINDA estão ativas e com status pendente ou pago, 
        // mas que a data base (next_due_date) já passou.
        $sites = Site::where('status', 'active')->get();

        $suspensosHoje = 0;
        $marcadosAtrasoHoje = 0;

        foreach ($sites as $site) {
            if (!$site->next_due_date) {
                continue;
            }

            $vencimento = Carbon::parse($site->next_due_date);

            // Se a data de vencimento passou hoje
            if ($hoje->greaterThan($vencimento)) {
                
                // Mudar status financeiro para overdue (atrasada)
                if ($site->billing_status !== 'overdue') {
                    $site->billing_status = 'overdue';
                    $site->save();
                    $marcadosAtrasoHoje++;
                    $this->warn("Banca [{$site->name}] marcada como ATRASADA.");
                }

                // Checar se já passou do limite de carência para SUSPENDER TUDO
                $dataLimite = $vencimento->copy()->addDays($carenciaDias);

                if ($hoje->greaterThan($dataLimite)) {
                    $site->status = 'suspended';
                    $site->save();
                    
                    // Derrubar o dono e os usuários
                    User::withoutGlobalScope('site')->where('site_id', $site->id)->update(['status' => 0]);
                    
                    $suspensosHoje++;
                    $this->error("Banca [{$site->name}] SUSPENSA por falta de pagamento (Mais de {$carenciaDias} dias).");
                }
            }
        }

        $this->info("Operação finalizada. Atrasados Hoje: {$marcadosAtrasoHoje} | Suspensos Hoje: {$suspensosHoje}");
        return Command::SUCCESS;
    }
}
