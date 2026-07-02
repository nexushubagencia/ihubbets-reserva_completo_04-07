<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

class CheckHighRiskBets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bets:check-high-risk';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for high-risk bets and alert the administrator';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Checking for high-risk bets...");
        
        $limitAmount = 1000; // Threshold for bet amount
        $limitPayout = 5000; // Threshold for potential payout
        
        $highRiskBets = \DB::table('bets')
            ->where('status', 'pending')
            ->where('created_at', '>=', now()->subMinutes(15))
            ->where(function ($query) use ($limitAmount, $limitPayout) {
                $query->where('amount', '>=', $limitAmount)
                      ->orWhere('potential_payout', '>=', $limitPayout);
            })
            ->get();
            
        if ($highRiskBets->count() > 0) {
            foreach ($highRiskBets as $bet) {
                $msg = "High Risk Bet Alert: Bet ID {$bet->id} | Amount: {$bet->amount} | Payout: {$bet->potential_payout} | Site ID: {$bet->site_id}";
                \Log::channel('single')->warning($msg);
                // Here we would integrate with Mail or a Telegram bot API
            }
            $this->info("Found " . $highRiskBets->count() . " high risk bets.");
        } else {
            $this->info("No new high risk bets found.");
        }
    }
}
