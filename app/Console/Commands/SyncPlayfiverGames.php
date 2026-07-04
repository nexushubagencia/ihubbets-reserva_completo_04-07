<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PlayfiverGame;
use App\Models\PlayfiverProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SyncPlayfiverGames extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'playfiver:sync-games
                            {--provider= : Filtrar por provider específico}
                            {--dry-run : Simular sem salvar no banco}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincroniza jogos e provedores da API Playfiver';

    public function handle()
    {
        $siteId = config('tenant.site_id', 1);
        $baseUrl = 'https://api.playfivers.com/api/v2';
        $token = config('services.playfiver.token', env('API_PLAYFIVER_TOKEN'));
        $secret = config('services.playfiver.secret', env('API_PLAYFIVER_SECRET'));

        if (!$token || !$secret) {
            $this->error('Credenciais Playfiver não configuradas.');
            return 1;
        }

        $this->info("Sincronizando provedores e jogos do Playfiver (site: {$siteId})...");

        try {
            // 1. Providers
            $providersResponse = Http::withHeaders([
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ])->get("{$baseUrl}/providers", [
                'agentToken' => $token,
                'secretKey' => $secret,
            ]);

            if (!$providersResponse->successful()) {
                $this->error("Erro ao buscar provedores: {$providersResponse->body()}");
                return 1;
            }

            $providersData = $providersResponse->json();
            $providers = $providersData['providers'] ?? $providersData['data'] ?? [];

            $this->info("Provedores encontrados: " . count($providers));

            // 2. Games
            $gamesResponse = Http::withHeaders([
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ])->get("{$baseUrl}/games", [
                'agentToken' => $token,
                'secretKey' => $secret,
                'provider' => $this->option('provider'),
            ]);

            if (!$gamesResponse->successful()) {
                $this->error("Erro ao buscar jogos: {$gamesResponse->body()}");
                return 1;
            }

            $gamesData = $gamesResponse->json();
            $games = $gamesData['games'] ?? $gamesData['data'] ?? [];

            $this->info("Jogos encontrados: " . count($games));

            if ($this->option('dry-run')) {
                $this->warn('Modo simulação — nenhum dado foi salvo.');
                return 0;
            }

            // Salva providers
            $savedProviders = 0;
            foreach ($providers as $p) {
                PlayfiverProvider::updateOrCreate(
                    ['provider_id' => $p['id'] ?? $p['provider_id'], 'site_id' => $siteId],
                    [
                        'name' => $p['name'] ?? ($p['provider_id'] ?? 'Provider'),
                        'image_url' => $p['image_url'] ?? null,
                        'wallet_name' => $p['wallet_name'] ?? null,
                        'status' => 1,
                    ]
                );
                $savedProviders++;
            }

            // Salva games
            $savedGames = 0;
            foreach ($games as $g) {
                PlayfiverGame::updateOrCreate(
                    ['game_code' => $g['game_code'] ?? $g['code'], 'site_id' => $siteId],
                    [
                        'name' => $g['name'] ?? ($g['game_name'] ?? 'Jogo'),
                        'image_url' => $g['image_url'] ?? $g['image'] ?? null,
                        'provider' => $g['provider'] ?? ($g['provider_id'] ?? 'Playfiver'),
                        'status' => 1,
                        'original' => $g['original'] ?? 0,
                        'is_popular' => $g['is_popular'] ?? 0,
                    ]
                );
                $savedGames++;
            }

            $this->info("Sincronizado: {$savedProviders} provedores e {$savedGames} jogos.");
            return 0;

        } catch (\Exception $e) {
            Log::error('SyncPlayfiverGames error: ' . $e->getMessage());
            $this->error('Erro: ' . $e->getMessage());
            return 1;
        }
    }
}
