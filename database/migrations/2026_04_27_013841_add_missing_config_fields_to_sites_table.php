<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            // Loto
            $table->decimal('menor_valor_loto', 12, 2)->default(1)->nullable();
            $table->decimal('max_valor_loto', 12, 2)->default(1000)->nullable();

            // Ao Vivo extras
            $table->integer('time_live')->default(80)->nullable();
            $table->decimal('cotacao_live', 8, 2)->default(1.01)->nullable();
            $table->string('futebol_ao_vivo', 5)->default('Sim')->nullable();

            // Geral / Financeiro
            $table->decimal('comissao_premio', 8, 2)->default(0)->nullable();
            $table->string('email_alerta')->nullable();
            $table->decimal('alerta_aposta_acima', 12, 2)->default(100)->nullable();
            $table->decimal('travar_odd_acima', 8, 2)->default(500)->nullable();

            // Permissões
            $table->string('cambista_pode_cancelar', 5)->default('Sim')->nullable();
            $table->integer('tempo_limite_camb_cancela_aposta')->default(30)->nullable();

            // Exibição
            $table->string('bloq_aposta_madrugada', 5)->default('Não')->nullable();
            $table->date('data_limite_jogos')->nullable();

            // Esportes Ativos
            $table->string('op_futebol', 5)->default('Sim')->nullable();
            $table->string('op_quininha', 5)->default('Sim')->nullable();
            $table->string('op_seninha', 5)->default('Sim')->nullable();
            $table->string('op_ufcbox', 5)->default('Sim')->nullable();
            $table->string('op_basquete', 5)->default('Sim')->nullable();
            $table->string('op_tenis', 5)->default('Sim')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->dropColumn([
                'menor_valor_loto', 'max_valor_loto', 'time_live', 'cotacao_live',
                'futebol_ao_vivo', 'comissao_premio', 'email_alerta', 'alerta_aposta_acima',
                'travar_odd_acima', 'cambista_pode_cancelar', 'tempo_limite_camb_cancela_aposta',
                'bloq_aposta_madrugada', 'data_limite_jogos',
                'op_futebol', 'op_quininha', 'op_seninha', 'op_ufcbox', 'op_basquete', 'op_tenis',
            ]);
        });
    }
};
