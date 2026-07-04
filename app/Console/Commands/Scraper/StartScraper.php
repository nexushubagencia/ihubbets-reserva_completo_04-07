<?php

namespace App\Console\Commands\Scraper;

use Illuminate\Console\Command;

class StartScraper extends Command
{
    protected $signature = 'scraper:start';

    protected $description = 'Inicia o processo Node.js do scraper Jogadinha em background';

    public function handle(): int
    {
        $pidFile = storage_path('logs/scraper.pid');

        if (file_exists($pidFile)) {
            $oldPid = trim(file_get_contents($pidFile));
            if ($this->isProcessRunning($oldPid)) {
                $this->error("O scraper já está rodando (PID: {$oldPid}).");
                return static::FAILURE;
            }
            $this->warn("PID file órfão encontrado. Removendo...");
            @unlink($pidFile);
        }

        $nodeVersion = null;
        exec('node --version 2>&1', $nodeVersion, $exitCode);
        if ($exitCode !== 0) {
            $this->error("Node.js não encontrado. Instale o Node.js antes de usar o scraper.");
            return static::FAILURE;
        }
        $this->info("Node.js detectado: " . ($nodeVersion[0] ?? 'desconhecido'));

        $scraperDir = base_path('scraper-jogadinha');
        $entryPoint = $scraperDir . '/scraper-jogadinha.js';
        if (!is_file($entryPoint)) {
            $this->error("Arquivo scraper-jogadinha.js não encontrado em: {$entryPoint}");
            return static::FAILURE;
        }

        $this->info("Iniciando scraper em background...");

        $logFile = storage_path('logs/scraper.log');
        $pid = $this->startBackgroundProcess($entryPoint, $logFile);

        if ($pid === null) {
            $this->error("Falha ao iniciar o scraper. Verifique os logs em {$logFile}");
            return static::FAILURE;
        }

        file_put_contents($pidFile, $pid);

        $this->info("Scraper iniciado com sucesso! PID: {$pid}");
        $this->info("Logs: {$logFile}");

        return static::SUCCESS;
    }

    private function startBackgroundProcess(string $entryPoint, string $logFile): ?string
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $cmd = sprintf(
                'start /B node "%s" > "%s" 2>&1',
                $entryPoint,
                $logFile
            );
            exec($cmd, $output, $exitCode);

            if ($exitCode !== 0) {
                return null;
            }

            usleep(500000);
            $pid = $this->findNodeProcess();
            return $pid;
        }

        $cmd = sprintf(
            'node "%s" > "%s" 2>&1 & echo $!',
            $entryPoint,
            $logFile
        );

        $output = null;
        exec($cmd, $output, $exitCode);

        if ($exitCode !== 0 || empty($output)) {
            return null;
        }

        return trim($output[0]);
    }

    private function isProcessRunning(string $pid): bool
    {
        // Sanitizar PID - deve ser um número inteiro válido
        if (!ctype_digit($pid) || (int)$pid <= 0) {
            return false;
        }

        if (PHP_OS_FAMILY === 'Windows') {
            $output = null;
            exec("tasklist /FI \"PID eq {$pid}\" 2>&1", $output);
            return str_contains(implode(' ', $output), $pid);
        }

        return file_exists("/proc/{$pid}");
    }

    private function findNodeProcess(): ?string
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $output = null;
            exec('tasklist /FI "IMAGENAME eq node.exe" /FO CSV /NH 2>&1', $output);
            foreach ($output as $line) {
                if (str_contains($line, 'node.exe')) {
                    $parts = str_getcsv($line);
                    if (isset($parts[1])) {
                        return $parts[1];
                    }
                }
            }
            return null;
        }

        $output = null;
        exec('pgrep -f "node.*scraper-jogadinha/scraper-jogadinha.js" 2>&1', $output);
        return !empty($output) ? trim($output[0]) : null;
    }
}
