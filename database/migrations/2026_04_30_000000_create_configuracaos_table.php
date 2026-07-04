<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('configuracaos')) {
            Schema::create('configuracaos', function (Blueprint $table) {
                $table->id();
                $table->foreignId('site_id')->constrained('sites')->onDelete('cascade');
                // Bet Limits
                $table->decimal('valor_mini_aposta', 10, 2)->default(2.00);
                $table->decimal('valor_max_aposta', 10, 2)->default(1000.00);
                $table->decimal('premio_max', 15, 2)->default(50000.00);
                // Loto Limits
                $table->decimal('menor_valor_loto', 10, 2)->default(1.00)->nullable();
                $table->decimal('max_valor_loto', 10, 2)->default(100.00)->nullable();
                // Ticket Config
                $table->decimal('cotacao_mini_bilhete', 10, 2)->default(1.01);
                $table->decimal('cotacao_max_bilhete', 10, 2)->default(10000.00)->nullable();
                $table->integer('quantidade_jogos_mini_bilhete')->default(1);
                $table->integer('quantidade_jogos_max_bilhete')->default(25);
                $table->integer('quantidade_times_visitantes_mesmo_camp')->default(3)->nullable();
                // Odd Controls
                $table->decimal('bloquear_odd_abaixo', 10, 2)->default(1.00);
                $table->decimal('travar_odd_acima', 10, 2)->default(1000.00);
                // Alerts
                $table->text('texto_rodape')->nullable();
                $table->string('email_alerta')->nullable();
                $table->decimal('alerta_aposta_acima', 10, 2)->default(500.00);
                // Cambista Rules
                $table->string('cambista_pode_cancelar')->default('Sim')->nullable();
                $table->integer('tempo_limite_camb_cancela_aposta')->default(5)->nullable();
                $table->string('gerente_pode_cancelar')->default('Sim')->nullable();
                // Operational
                $table->string('aposta_ativa')->default('Sim');
                $table->string('bloq_aposta_madrugada')->default('Sim');
                $table->date('data_limite_jogos')->nullable();
                // Sports Toggles
                $table->string('op_futebol')->default('Sim');
                $table->string('op_ufcbox')->default('Sim')->nullable();
                $table->string('op_quininha')->default('Sim')->nullable();
                $table->string('op_seninha')->default('Sim')->nullable();
                $table->string('op_basquete')->default('Sim');
                $table->string('op_tenis')->default('Sim');
                $table->string('op_volei')->default('Sim');
                $table->string('op_e_sports')->default('Sim');
                // Live
                $table->string('futebol_ao_vivo')->default('Sim')->nullable();
                $table->integer('time_live')->default(30)->nullable();
                $table->decimal('cotacao_live', 10, 2)->default(1.50)->nullable();
                // Commissions
                $table->decimal('comissao_premio', 5, 2)->default(0)->nullable();
                // Bonus/Payment
                $table->decimal('max_bonus_conversion', 10, 2)->default(0)->nullable();
                $table->decimal('min_deposit', 10, 2)->default(10)->nullable();
                $table->decimal('max_deposit', 10, 2)->default(5000)->nullable();
                $table->decimal('min_withdrawal', 10, 2)->default(50)->nullable();
                $table->decimal('max_withdrawal', 10, 2)->default(5000)->nullable();
                $table->integer('withdrawal_limit_day')->default(3)->nullable();
                // Affiliate %
                $table->decimal('perc_sub_lv1', 5, 2)->default(0)->nullable();
                $table->decimal('perc_sub_lv2', 5, 2)->default(0)->nullable();
                $table->decimal('perc_sub_lv3', 5, 2)->default(0)->nullable();
                // Payment Gateway
                $table->string('suitpay_client_id')->nullable();
                $table->string('suitpay_client_secret')->nullable();
                $table->string('active_deposit_gateway')->default('primepag')->nullable();
                $table->string('active_withdrawal_gateway')->default('primepag')->nullable();
                // Theme Colors
                $table->string('nome_plataforma')->nullable();
                $table->string('cor_principal')->nullable();
                $table->string('cor_secundaria')->nullable();
                $table->string('cor_fundo')->nullable();
                $table->string('cor_texto')->nullable();
                $table->string('cor_botoes')->nullable();
                $table->string('cor_botoes_perfil')->nullable();
                $table->string('cor_fundo_campeonato')->nullable();
                // Cash Out
                $table->string('cash_out_ativo')->default('Não')->nullable();
                $table->decimal('cash_out_taxa', 5, 2)->default(10)->nullable();
                // Affiliate
                $table->boolean('affiliate_enabled')->default(false)->nullable();
                $table->decimal('affiliate_commission', 5, 2)->default(0)->nullable();
                // WhatsApp / Help
                $table->string('link_whatsapp')->nullable();
                $table->string('status_whatsapp')->default('Não')->nullable();
                $table->string('link_ajuda')->nullable();
                // Live Odds Config
                $table->integer('live_valor_mini_aposta')->default(2)->nullable();
                $table->integer('live_valor_max_aposta')->default(500)->nullable();
                $table->integer('live_premio_max')->default(10000)->nullable();
                $table->decimal('live_cotacao_mini_bilhete', 10, 2)->default(2.00)->nullable();
                $table->integer('live_quantidade_jogos_mini_bilhete')->default(1)->nullable();
                $table->integer('live_quantidade_jogos_max_bilhete')->default(15)->nullable();
                $table->decimal('cotacao_mini_bilhete_mult', 10, 2)->default(1.40)->nullable();
                $table->string('hours_limit_date')->default('23:59:59')->nullable();
                $table->integer('limite_apostas_iguais')->default(0)->nullable();
                // Scraper Config
                $table->string('scraper_mode')->default('master')->nullable();
                $table->string('scraper_url')->nullable();
                $table->string('scraper_token')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('configuracaos');
    }
};
