<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PlayfiverGame;
use App\Models\PlayfiverProvider;

class PlayfiverDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $siteId = config('tenant.site_id', 1);

        $providers = [
            ['provider_id' => 1, 'name' => 'PG Soft', 'image_url' => null, 'wallet_name' => 'PG'],
            ['provider_id' => 2, 'name' => 'Pragmatic Play', 'image_url' => null, 'wallet_name' => 'PP'],
            ['provider_id' => 3, 'name' => 'Evolution', 'image_url' => null, 'wallet_name' => 'EVO'],
        ];

        foreach ($providers as $p) {
            PlayfiverProvider::updateOrCreate(
                ['provider_id' => $p['provider_id'], 'site_id' => $siteId],
                array_merge($p, ['status' => 1, 'site_id' => $siteId])
            );
        }

        $games = [
            ['game_code' => 'pg-fortune', 'name' => 'Fortune Tiger', 'provider' => 'PG Soft', 'is_popular' => 1],
            ['game_code' => 'pg-dragon', 'name' => 'Dragon Hatch', 'provider' => 'PG Soft', 'is_popular' => 1],
            ['game_code' => 'pp-gates', 'name' => 'Gates of Olympus', 'provider' => 'Pragmatic Play', 'is_popular' => 1],
            ['game_code' => 'pp-sweet', 'name' => 'Sweet Bonanza', 'provider' => 'Pragmatic Play', 'is_popular' => 1],
            ['game_code' => 'pp-starlight', 'name' => 'Starlight Princess', 'provider' => 'Pragmatic Play', 'is_popular' => 0],
            ['game_code' => 'evo-roulette', 'name' => 'Live Roulette', 'provider' => 'Evolution', 'is_popular' => 0],
            ['game_code' => 'evo-blackjack', 'name' => 'Live Blackjack', 'provider' => 'Evolution', 'is_popular' => 0],
        ];

        foreach ($games as $g) {
            PlayfiverGame::updateOrCreate(
                ['game_code' => $g['game_code'], 'site_id' => $siteId],
                array_merge($g, [
                    'status' => 1,
                    'original' => 0,
                    'image_url' => 'https://via.placeholder.com/400x300/1e293b/94a3b8?text=' . urlencode($g['name']),
                    'site_id' => $siteId,
                ])
            );
        }
    }
}
