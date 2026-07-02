<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Site;
use App\Models\User;
use App\Models\Bet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TenantWeeklyBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:backup-weekly';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Realiza backup automático semanal de todos os tenants (bancas) isoladamente.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando ciclo de backup semanal para todos os tenants...');
        
        $sites = Site::all();

        foreach ($sites as $site) {
            $this->info("Processando banca: {$site->name}");

            // 1. Limpeza de backups antigos (Rotação de 1 semana)
            $backupPath = "backups/tenants/site_{$site->id}";
            Storage::deleteDirectory($backupPath);
            Storage::makeDirectory($backupPath);

            // 2. Coleta profunda de dados
            $backup = [
                'metadata' => [
                    'site_name' => $site->name,
                    'site_id' => $site->id,
                    'domain' => $site->domain,
                    'backup_date' => now()->toDateTimeString(),
                    'version' => 'IHUB V2 - Auto Backup System'
                ],
                'site' => $site->toArray(),
                'settings' => DB::table('site_settings')->where('site_id', $site->id)->first(),
                'users' => User::withoutGlobalScope('site')->where('site_id', $site->id)->get()->toArray(),
                'bets' => Bet::withoutGlobalScope('site')
                            ->where('site_id', $site->id)
                            ->with(['items'])
                            ->get()->toArray(),
                'financial' => DB::table('transactions')
                                ->whereIn('user_id', function($query) use ($site) {
                                    $query->select('id')->from('master_users')->where('site_id', $site->id);
                                })->get()->toArray(),
                'banners' => DB::table('banners')->where('site_id', $site->id)->get()->toArray(),
                'pages' => DB::table('site_pages')->where('site_id', $site->id)->get()->toArray(),
                'manual_events' => DB::table('manual_events')
                                    ->where('site_id', $site->id)
                                    ->get()->map(function($event) {
                                        $event->markets = DB::table('manual_markets')
                                            ->where('event_id', $event->id)
                                            ->get()->map(function($market) {
                                                $market->odds = DB::table('manual_odds')->where('market_id', $market->id)->get();
                                                return $market;
                                            });
                                        return $event;
                                    })->toArray()
            ];

            // 3. Salvar arquivo
            $fileName = 'AUTO_BACKUP_' . strtoupper(Str::slug($site->name)) . '.json';
            Storage::put("{$backupPath}/{$fileName}", json_encode($backup, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            $this->comment("Backup concluído para: {$site->name}");
        }

        $this->info('Todos os backups semanais foram atualizados com sucesso!');
    }
}
