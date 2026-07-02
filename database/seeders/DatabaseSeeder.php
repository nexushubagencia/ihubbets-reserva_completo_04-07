<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Criar Site Padrão (Demo)
        $site = \App\Models\Site::create([
            'uuid' => (string) Str::uuid(),
            'site_id' => 'ihubbets',
            'name' => 'IHUB BETS PRO',
            'complete_name' => 'IHUB BETS - APOSTAS ESPORTIVAS',
            'first_letter' => 'I',
            'second_letter' => 'H',
            'domain' => 'localhost',
            'status' => 'active',
            'theme_color' => 'verde-claro',
            'active_sports' => 1,
            'display_modalities' => 'sports'
        ]);

        // 2. Criar Configurações de Aposta Padrão
        \App\Models\SiteSetting::create([
            'site_id' => $site->id,
            'valor_mini_aposta' => 1.00,
            'valor_max_aposta' => 1000.00,
            'premio_max' => 50000.00,
        ]);

        // 3. Criar Super Admin
        \App\Models\User::create([
            'name' => 'Administrador iHub',
            'username' => 'admin',
            'email' => 'admin@ihub.com',
            'password' => Hash::make('admin123'),
            'role' => 'super_admin',
            'site_id' => $site->id,
            'status' => 1
        ]);

        // 4. Criar Banners Iniciais
        \App\Models\Banner::create([
            'site_id' => $site->id,
            'title' => 'Bem-vindo ao IHUB',
            'image_path' => '/img/banner1.jpg', // Usando caminhos locais presumidos
            'position' => 'home_main',
            'status' => 1
        ]);

        // 5. Criar Confrontos de Exemplo
        $match = \App\Models\Game::create([
            'event_id' => 'DEMO_001',
            'sport_id' => '1',
            'sport_name' => 'Futebol',
            'league' => 'Brasileirão Série A',
            'home' => 'Flamengo',
            'away' => 'Palmeiras',
            'date' => Carbon::now()->addHours(2),
            'time' => Carbon::now()->addHours(2)->format('H:i'),
            'visible' => 'Sim',
            'time_status' => 0
        ]);

        // 6. Criar Mercado e Odds de Exemplo
        $mercado = \App\Models\Mercado::create([
            'name' => 'Resultado Final',
            'market_id' => '1'
        ]);

        \App\Models\Odd::create(['event_id' => 'DEMO_001', 'market_id' => '1', 'name' => 'Flamengo', 'value' => 1.95]);
        \App\Models\Odd::create(['event_id' => 'DEMO_001', 'market_id' => '1', 'name' => 'Empate', 'value' => 3.40]);
        \App\Models\Odd::create(['event_id' => 'DEMO_001', 'market_id' => '1', 'name' => 'Palmeiras', 'value' => 3.80]);
    }
}
