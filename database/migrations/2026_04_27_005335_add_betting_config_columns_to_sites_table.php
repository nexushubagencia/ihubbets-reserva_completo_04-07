<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            // --- PRÉ-JOGO ---
            $table->decimal('prem_max_pre', 12, 2)->default(50000)->nullable();
            $table->decimal('val_min_pre', 12, 2)->default(1)->nullable();
            $table->decimal('val_max_pre', 12, 2)->default(1000)->nullable();
            $table->decimal('cot_min_pre', 8, 2)->default(1.40)->nullable();
            $table->decimal('cot_max_pre', 8, 2)->default(1000)->nullable();
            $table->integer('qtd_min_pre')->default(1)->nullable();
            $table->integer('qtd_max_pre')->default(12)->nullable();
            $table->decimal('odd_max_pre', 8, 2)->default(100)->nullable();
            $table->decimal('block_odds_below', 8, 2)->default(1)->nullable();
            $table->integer('min_valid_pin')->default(500)->nullable();
            $table->integer('min_before_game')->default(0)->nullable();

            // --- AO VIVO ---
            $table->integer('qtd_min_live')->default(1)->nullable();
            $table->decimal('val_min_live', 12, 2)->default(2)->nullable();
            $table->decimal('val_max_live', 12, 2)->default(500)->nullable();
            $table->decimal('cot_min_live', 8, 2)->default(2)->nullable();
            $table->decimal('cot_max_live', 8, 2)->default(1000)->nullable();
            $table->decimal('odd_max_live', 8, 2)->default(100)->nullable();
            $table->decimal('cot_min_comm', 8, 2)->default(2)->nullable();
            $table->decimal('prem_max_live', 12, 2)->default(10000)->nullable();
            $table->integer('accept_bet_until')->default(90)->nullable();
            $table->decimal('alt_cot_live', 8, 2)->default(0)->nullable();

            // --- GERAL ---
            $table->decimal('prem_max_equal', 12, 2)->default(0)->nullable();
            $table->boolean('active_bets')->default(true)->nullable();
            $table->boolean('merge_pre_live')->default(true)->nullable();
            $table->string('site_lang', 10)->default('pt_BR')->nullable();
            $table->boolean('lang_selector')->default(false)->nullable();
            $table->integer('cancel_time_minutes')->default(10)->nullable();

            // --- INTEGRAÇÕES ---
            $table->text('ga_code')->nullable();
            $table->string('pixel_id')->nullable();
            $table->boolean('ga_enabled')->default(false)->nullable();
            $table->boolean('pixel_enabled')->default(false)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->dropColumn([
                'prem_max_pre', 'val_min_pre', 'val_max_pre', 'cot_min_pre', 'cot_max_pre',
                'qtd_min_pre', 'qtd_max_pre', 'odd_max_pre', 'block_odds_below',
                'min_valid_pin', 'min_before_game',
                'qtd_min_live', 'val_min_live', 'val_max_live', 'cot_min_live', 'cot_max_live',
                'odd_max_live', 'cot_min_comm', 'prem_max_live', 'accept_bet_until', 'alt_cot_live',
                'prem_max_equal', 'active_bets', 'merge_pre_live', 'site_lang', 'lang_selector',
                'cancel_time_minutes',
                'ga_code', 'pixel_id', 'ga_enabled', 'pixel_enabled',
            ]);
        });
    }
};
