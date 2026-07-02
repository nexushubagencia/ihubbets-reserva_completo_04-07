<?php

namespace App\Console\Commands\Scraper;

use Illuminate\Console\Command;

class StopScraper extends Command
{
    protected $signature = 'scraper:stop';

    protected $description = 'Para o processo do scraper Jogadinha';

    public function handle(): int
    {
        $pidFile = storage_path('logs/scraper.pid');

        if (!file_exists($pidFile)) {
            $this->warn("Nenhum PID file encontrado. O scraper pode não estar rodando.");
            return static::SUCCESS;
        }

        $pid = trim(file_get_contents($pidFile));

        if (empty($pid)) {
            $this->warn("PID file está vazio. Removendo...");
            @unlink($pidFile);
            return static::SUCCESS;
        }

        $this->info("Parando scraper (PID: {$pid})...");

        if ($this->killProcess($pid)) {
            $this->info("Processo {$pid} finalizado com sucesso.");
        } else {
            $this->warn("Processo {$pid} pode já ter sido finalizado.");
        }

        @unlink($pidFile);

        $this->info("Scraper parado e PID file removido.");
        return static::SUCCESS;
    }

    private function killProcess(string $pid): bool
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $output = null;
            exec("taskkill /PID {$pid} /F 2>&1", $output, $exitCode);
            return $exitCode === 0;
        }

        posix_kill((int) $pid, SIGTERM);
        usleep(500000);

        if (file_exists("/proc/{$pid}")) {
            posix_kill((int) $pid, SIGKILL);
        }

        return !file_exists("/proc/{$pid}");
    }
}
