<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ManualEvent;
use App\Models\ManualOdd;
use App\Models\ManualMarket;
use App\Models\Bet;
use App\Models\BetItem;
use App\Models\User;
use Illuminate\Support\Str;

class TestMarketsSeeder extends Seeder
{
    public function run()
    {
        // Limpar dados anteriores para evitar duplicidade
        \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();
        \Illuminate\Support\Facades\DB::table('bet_items')->truncate();
        \Illuminate\Support\Facades\DB::table('bets')->truncate();
        \Illuminate\Support\Facades\DB::table('manual_odds')->truncate();
        \Illuminate\Support\Facades\DB::table('manual_markets')->truncate();
        \Illuminate\Support\Facades\DB::table('manual_events')->truncate();
        \Illuminate\Support\Facades\DB::table('manual_categories')->truncate();
        \Illuminate\Support\Facades\DB::table('sites')->truncate();
        \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();

        // 0. Criar o Site Mestre (Obrigatório para FK)
        \Illuminate\Support\Facades\DB::table('sites')->insert([
            'id' => 1,
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'name' => 'IHUB BETS MASTER',
            'domain' => 'localhost',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 1. Criar Categorias Manuais
        $x1Cat = \Illuminate\Support\Facades\DB::table('manual_categories')->insertGetId([
            'name' => 'X1 KING LEAGUE',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $kingCat = \Illuminate\Support\Facades\DB::table('manual_categories')->insertGetId([
            'name' => 'KING LEAGUE ESPIRITO SANTO',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. Criar Eventos X1 Brazil (Hoje)
        $matches = [
            ['time' => '18:00', 'home' => 'L7', 'away' => 'Bolt'],
            ['time' => '19:15', 'home' => 'Etinho', 'away' => 'Brasil'],
            ['time' => '20:30', 'home' => 'Berô', 'away' => 'Bode'],
            ['time' => '21:45', 'home' => 'Daniel Coringa', 'away' => 'Paçoca'],
        ];

        foreach ($matches as $match) {
            $eventId = \Illuminate\Support\Facades\DB::table('manual_events')->insertGetId([
                'site_id' => 1,
                'category_id' => $x1Cat,
                'title' => "{$match['home']} vs {$match['away']} (X1 BRAZIL)",
                'start_time' => date('Y-m-d ') . $match['time'] . ':00', 
                'status' => 'open',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $marketId = \Illuminate\Support\Facades\DB::table('manual_markets')->insertGetId([
                'event_id' => $eventId,
                'name' => 'Vencedor do Encontro',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            \Illuminate\Support\Facades\DB::table('manual_odds')->insert([
                ['market_id' => $marketId, 'label' => $match['home'], 'value' => 1.90, 'created_at' => now(), 'updated_at' => now()],
                ['market_id' => $marketId, 'label' => $match['away'], 'value' => 1.90, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        // 3. Criar Eventos KINGS LEAGUE BRAZIL (Hoje)
        $kingMatches = [
            ['time' => '17:00', 'home' => 'DesimpaiN', 'away' => 'Podpah Funkbol Clube'],
            ['time' => '18:00', 'home' => 'Furia', 'away' => 'Dibrados FC'],
            ['time' => '19:00', 'home' => 'Loud', 'away' => 'Capim'],
            ['time' => '20:00', 'home' => 'Dendele', 'away' => 'Fluxo'],
            ['time' => '21:00', 'home' => 'G3X', 'away' => 'Nyvelados'],
        ];

        foreach ($kingMatches as $match) {
            $eventId = \Illuminate\Support\Facades\DB::table('manual_events')->insertGetId([
                'site_id' => 1,
                'category_id' => $kingCat,
                'title' => "{$match['home']} vs {$match['away']} (KINGS LEAGUE BRAZIL)",
                'start_time' => date('Y-m-d ') . $match['time'] . ':00',
                'status' => 'open',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $marketId = \Illuminate\Support\Facades\DB::table('manual_markets')->insertGetId([
                'event_id' => $eventId,
                'name' => 'Resultado Final',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            \Illuminate\Support\Facades\DB::table('manual_odds')->insert([
                ['market_id' => $marketId, 'label' => 'Casa', 'value' => 2.40, 'created_at' => now(), 'updated_at' => now()],
                ['market_id' => $marketId, 'label' => 'Empate', 'value' => 3.10, 'created_at' => now(), 'updated_at' => now()],
                ['market_id' => $marketId, 'label' => 'Fora', 'value' => 2.20, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        // 4. Criar Master Admin e uma Aposta de Teste
        \Illuminate\Support\Facades\DB::table('master_users')->truncate();
        $adminId = \Illuminate\Support\Facades\DB::table('master_users')->insertGetId([
            'site_id' => 1,
            'name' => 'Super Admin Master',
            'username' => 'admin',
            'password' => bcrypt('admin123'),
            'role' => 'admin',
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $betId = \Illuminate\Support\Facades\DB::table('bets')->insertGetId([
            'site_id' => 1,
            'user_id' => $adminId,
            'external_code' => strtoupper(\Illuminate\Support\Str::random(10)),
            'type' => 'multiple',
            'amount' => 100.00,
            'potential_payout' => 450.00,
            'status' => 'open',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        \Illuminate\Support\Facades\DB::table('bet_items')->insert([
            [
                'bet_id' => $betId,
                'match_id' => \Illuminate\Support\Facades\DB::table('manual_events')->where('title', 'LIKE', '%L7 vs Bolt%')->first()->id,
                'selection_label' => 'L7',
                'selection_odd' => 1.90,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'bet_id' => $betId,
                'match_id' => \Illuminate\Support\Facades\DB::table('manual_events')->where('title', 'LIKE', '%DesimpaiN vs Podpah%')->first()->id,
                'selection_label' => 'Casa',
                'selection_odd' => 2.40,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
