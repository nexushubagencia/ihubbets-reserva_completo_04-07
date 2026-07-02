<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ProductionCleanup extends Command
{
    protected $signature = 'ihub:cleanup';
    protected $description = 'Remove all test/fake data and prepare for production';

    public function handle()
    {
        $this->info('Starting production cleanup...');

        // 1. Identify test users
        $testUserIds = DB::table('master_users')
            ->whereNull('site_id')
            ->orWhere('site_id', 5)
            ->pluck('id')
            ->toArray();

        if (count($testUserIds) > 0) {
            $this->info('Removing ' . count($testUserIds) . ' test users and their data...');

            // Remove associated data
            DB::table('bets')->whereIn('user_id', $testUserIds)->delete();
            DB::table('transactions')->whereIn('user_id', $testUserIds)->delete();
            DB::table('pix_deposits')->whereIn('user_id', $testUserIds)->delete();
            DB::table('withdrawal_requests')->whereIn('user_id', $testUserIds)->delete();
            DB::table('bonus_user')->whereIn('user_id', $testUserIds)->delete();
            DB::table('affiliates')->whereIn('user_id', $testUserIds)->delete();
            
            // Finally remove users
            DB::table('master_users')->whereIn('id', $testUserIds)->delete();
        }

        // 2. Remove Site DEMO
        DB::table('sites')->where('id', 5)->delete();

        // 3. Clear other tables that might have demo content
        // Banners from site 5
        DB::table('banners')->where('site_id', 5)->delete();
        
        // Featured matches from site 5
        DB::table('featured_matches')->where('site_id', 5)->delete();

        $this->info('Cleanup completed successfully.');
    }
}
