<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use App\Models\Game;

class NacionalizeAssets extends Command
{
    protected $signature = 'assets:nacionalize {--force : Force download even if exists}';
    protected $description = 'Download all external assets (flags/logos) to local storage for sovereignty.';

    private $baseUrl = "https://d2x9mcd4yw5kj3.cloudfront.net/";

    public function handle()
    {
        $this->info("Iniciando nacionalização de ativos...");

        // 1. Povoar Bandeiras de Times (Soccer)
        $this->downloadMatchFlags();

        // 2. Outros ativos fixos (Logo Pix, etc)
        $this->downloadFixedAssets();

        $this->info("Processo concluído!");
    }

    private function downloadMatchFlags()
    {
        $games = Game::select('image_id_home', 'image_id_away')->get();
        $total = $games->count() * 2;
        $bar = $this->output->createProgressBar($total);

        $types = ['soccer/m', 'soccer/b'];

        foreach ($games as $game) {
            foreach ($types as $type) {
                $this->downloadFlag($type, $game->image_id_home);
                $bar->advance();
                $this->downloadFlag($type, $game->image_id_away);
                $bar->advance();
            }
        }

        $bar->finish();
        $this->newLine();
    }

    private function downloadFlag($type, $id)
    {
        if (!$id || $id == '0') return;

        $path = "assets/flags/{$type}/{$id}.png";
        $localPath = public_path($path);

        if (File::exists($localPath) && !$this->option('force')) {
            return;
        }

        $remoteUrl = $this->baseUrl . "flags/{$type}/{$id}.png";

        try {
            $response = Http::get($remoteUrl);
            if ($response->successful()) {
                File::ensureDirectoryExists(dirname($localPath));
                File::put($localPath, $response->body());
            }
        } catch (\Exception $e) {
            // Silently fail for individual images to keep progress
        }
    }

    private function downloadFixedAssets()
    {
        $fixed = [
            'img/logo_pix.png',
            'assets/img/default-shield.png'
        ];

        foreach ($fixed as $asset) {
            $localPath = public_path($asset);
            if (!File::exists($localPath)) {
                $remoteUrl = $this->baseUrl . $asset;
                try {
                    $response = Http::get($remoteUrl);
                    if ($response->successful()) {
                        File::ensureDirectoryExists(dirname($localPath));
                        File::put($localPath, $response->body());
                    }
                } catch (\Exception $e) {}
            }
        }
    }
}
