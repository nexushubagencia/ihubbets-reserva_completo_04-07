<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adiciona colunas essenciais que faltam na tabela 'bets' para suportar
     * o fluxo completo de apostas do frontend (Geral.vue).
     * 
     * Colunas adicionadas:
     * - ticket_code: código do bilhete (PIN) exibido no modal
     * - selections: JSON com os palpites do bilhete  
     * - client_name: nome do apostador
     * - cashout_pin: PIN de cashout
     * - cash_out_amount: valor do cashout
     * - can_cash_out: flag de permissão de cashout
     * - is_bonus_bet: flag se é aposta com bônus
     */
    public function up(): void
    {
        Schema::table('bets', function (Blueprint $table) {
            if (!Schema::hasColumn('bets', 'ticket_code')) {
                $table->string('ticket_code', 20)->nullable()->after('external_code');
            }
            if (!Schema::hasColumn('bets', 'selections')) {
                $table->json('selections')->nullable()->after('amount');
            }
            if (!Schema::hasColumn('bets', 'client_name')) {
                $table->string('client_name')->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('bets', 'cashout_pin')) {
                $table->string('cashout_pin')->nullable();
            }
            if (!Schema::hasColumn('bets', 'cash_out_amount')) {
                $table->decimal('cash_out_amount', 15, 2)->nullable();
            }
            if (!Schema::hasColumn('bets', 'can_cash_out')) {
                $table->boolean('can_cash_out')->default(false);
            }
            if (!Schema::hasColumn('bets', 'is_bonus_bet')) {
                $table->boolean('is_bonus_bet')->default(false);
            }
        });
    }

    public function down(): void
    {
        Schema::table('bets', function (Blueprint $table) {
            $cols = ['ticket_code', 'selections', 'client_name', 'cashout_pin', 'cash_out_amount', 'can_cash_out', 'is_bonus_bet'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('bets', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
