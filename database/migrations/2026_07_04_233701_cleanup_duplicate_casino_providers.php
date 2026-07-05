<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Delete providers with empty name or junk codes
        $junkCodes = ['sport_betting', 'fast_games', 'live_dealers', 'fish', 'firekirin'];
        DB::table('casino_providers')
            ->where('name', '')
            ->orWhereIn('code', $junkCodes)
            ->delete();

        // 2. Find duplicate providers by normalized name (lowercase, trimmed)
        $providers = DB::table('casino_providers')->get();
        $grouped = [];
        foreach ($providers as $p) {
            $key = strtolower(trim($p->name));
            if (!isset($grouped[$key])) {
                $grouped[$key] = [];
            }
            $grouped[$key][] = $p->id;
        }

        // 3. For each group of duplicates, keep the one with most games, delete the rest
        foreach ($grouped as $name => $ids) {
            if (count($ids) <= 1) continue;

            // Count games for each provider
            $counts = [];
            foreach ($ids as $id) {
                $counts[$id] = DB::table('casino_games')->where('provider_id', $id)->count();
            }

            // Sort by game count descending, keep the first (most games)
            arsort($counts);
            $keepId = array_key_first($counts);
            $deleteIds = array_diff($ids, [$keepId]);

            // Move games from deleted providers to the kept one
            foreach ($deleteIds as $deleteId) {
                DB::table('casino_games')->where('provider_id', $deleteId)->update(['provider_id' => $keepId]);
                DB::table('casino_providers')->where('id', $deleteId)->delete();
            }
        }

        // 4. Normalize remaining provider names (capitalize properly)
        $nameMap = [
            '3oaks' => '3 Oaks',
            '7777 gaming' => '7777 Gaming',
            'ainsworth' => 'Ainsworth',
            'altente' => 'Altente',
            'amatic' => 'Amatic',
            'apex' => 'Apex',
            'apollo' => 'Apollo',
            'apparat' => 'Apparat',
            'aristocrat' => 'Aristocrat',
            'bet games' => 'Bet Games',
            'bet soft' => 'BetSoft',
            'bgaming' => 'BGaming',
            'big' => 'Big Casino',
            'big casino' => 'Big Casino',
            'booming' => 'Booming Games',
            'booongo' => 'Booongo',
            'cq9' => 'CQ9',
            'dream' => 'Dream Casino',
            'dreamtech' => 'DreamTech',
            'egt' => 'EGT',
            'evoplay' => 'Evoplay',
            'ezugi' => 'Ezugi',
            'fast_games' => 'Fast Games',
            'galaxsys' => 'Galaxsys',
            'gameart' => 'GameArt',
            'genesis' => 'Genesis',
            'igrosoft' => 'Igrosoft',
            'igt' => 'IGT',
            'ka gaming' => 'Ka Gaming',
            'kajot' => 'Kajot',
            'livevegas' => 'LiveVegas',
            'mascot' => 'Mascot',
            'merkur' => 'Merkur',
            'micro' => 'Microgaming',
            'micro casino' => 'Microgaming',
            'microgaming' => 'Microgaming',
            'netent' => 'NetEnt',
            'novomatic' => 'Novomatic',
            'onlyplay' => 'Onlyplay',
            'pgsoft' => 'PG Soft',
            'playngo' => 'Play\'n GO',
            'playson' => 'Playson',
            'playstar' => 'PlayStar',
            'popiplay' => 'Popiplay',
            'pragmatic' => 'Pragmatic Play',
            'quickspin' => 'Quickspin',
            'readyplay' => 'ReadyPlay',
            'red_tiger' => 'Red Tiger',
            'red_tiger_premium' => 'Red Tiger',
            'redtiger' => 'Red Tiger',
            'reelkingdom' => 'Reel Kingdom',
            'retrogaming' => 'RetroGaming',
            'rubyplay' => 'RubyPlay',
            'salsa studio' => 'Salsa Studio',
            'scientific_games' => 'Scientific Games',
            'smart soft' => 'SmartSoft',
            'spinomenal' => 'Spinomenal',
            'spribe' => 'Spribe',
            'tomhorn' => 'Tom Horn',
            'toptrend' => 'TopTrend Gaming',
            'wazdan' => 'Wazdan',
            'wizard' => 'Wizard',
        ];

        foreach ($nameMap as $lower => $proper) {
            DB::table('casino_providers')
                ->whereRaw('LOWER(name) = ?', [$lower])
                ->update(['name' => $proper]);
        }
    }

    public function down(): void
    {
        // Cannot reverse cleanup
    }
};
