<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImportCasinoV3Data extends Command
{
    protected $signature = 'casino:import-v3-data {file=database/seeders/casino_v3_import.sql}';
    protected $description = 'Importa dados do Cassino-v3 (providers, categories, games)';

    public function handle(): int
    {
        $file = base_path($this->argument('file'));

        if (!file_exists($file)) {
            $this->error('Arquivo não encontrado: ' . $file);
            return 1;
        }

        $sql = file_get_contents($file);

        // Split by semicolon followed by newline to avoid breaking values
        $statements = array_filter(array_map('trim', preg_split('/;\s*\n/', $sql)));

        $this->info('Importando ' . count($statements) . ' statements...');

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        $errors = [];
        $success = 0;

        foreach ($statements as $statement) {
            if (empty($statement)) {
                continue;
            }
            try {
                DB::unprepared($statement);
                $success++;
            } catch (\Exception $e) {
                $errors[] = $e->getMessage();
                Log::error('Import casino v3 error: ' . $e->getMessage() . ' | SQL: ' . substr($statement, 0, 200));
                $this->warn('Erro em statement: ' . substr($statement, 0, 100) . '... ' . $e->getMessage());
            }
        }

        if (!empty($errors)) {
            $this->error('Importação concluída com ' . count($errors) . ' erro(s).');
        } else {
            $this->info('Importação concluída: ' . $success . ' statement(s) executado(s) com sucesso.');
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $counts = [
            'categories' => DB::table('casino_categories')->count(),
            'providers' => DB::table('casino_providers')->count(),
            'games' => DB::table('casino_games')->count(),
            'category_game' => DB::table('casino_category_game')->count(),
        ];

        $this->info('Importação concluída:');
        foreach ($counts as $table => $count) {
            $this->line("  - {$table}: {$count}");
        }

        return 0;
    }
}
