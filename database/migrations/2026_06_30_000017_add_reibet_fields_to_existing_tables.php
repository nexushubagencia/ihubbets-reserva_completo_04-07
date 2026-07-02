<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // === CAMPOS REI BET PARA TABELA master_users ===
        Schema::table('master_users', function (Blueprint $table) {
            // Sistema de bônus/rollover (do REI BET)
            if (!Schema::hasColumn('master_users', 'credito')) {
                $table->decimal('credito', 12, 2)->default(0)->after('balance');
            }
            if (!Schema::hasColumn('master_users', 'saldo_bonus')) {
                $table->decimal('saldo_bonus', 12, 2)->default(0)->after('credito');
            }
            if (!Schema::hasColumn('master_users', 'rollover_meta')) {
                $table->decimal('rollover_meta', 12, 2)->default(0)->after('saldo_bonus');
            }
            if (!Schema::hasColumn('master_users', 'rollover_atual')) {
                $table->decimal('rollover_atual', 12, 2)->default(0)->after('rollover_meta');
            }
            if (!Schema::hasColumn('master_users', 'promocao_ativa_id')) {
                $table->unsignedInteger('promocao_ativa_id')->nullable()->after('rollover_atual');
            }
            if (!Schema::hasColumn('master_users', 'verified')) {
                $table->boolean('verified')->default(false)->after('promocao_ativa_id');
            }
            if (!Schema::hasColumn('master_users', 'nascimento')) {
                $table->date('nascimento')->nullable()->after('birth_date');
            }
        });

        // === CAMPOS REI BET PARA TABELA configuracaos ===
        Schema::table('configuracaos', function (Blueprint $table) {
            // Theme colors (do REI BET)
            if (!Schema::hasColumn('configuracaos', 'cor_principal')) {
                $table->string('cor_principal')->nullable()->default('#1b1b1b')->after('op_volei');
            }
            if (!Schema::hasColumn('configuracaos', 'cor_secundaria')) {
                $table->string('cor_secundaria')->nullable()->default('#2ac2ba')->after('cor_principal');
            }
            if (!Schema::hasColumn('configuracaos', 'cor_fundo')) {
                $table->string('cor_fundo')->nullable()->default('#0a0e12')->after('cor_secundaria');
            }
            if (!Schema::hasColumn('configuracaos', 'cor_texto')) {
                $table->string('cor_texto')->nullable()->default('#ffffff')->after('cor_fundo');
            }
            if (!Schema::hasColumn('configuracaos', 'cor_botoes')) {
                $table->string('cor_botoes')->nullable()->after('cor_texto');
            }
            if (!Schema::hasColumn('configuracaos', 'cor_botoes_perfil')) {
                $table->string('cor_botoes_perfil')->nullable()->after('cor_botoes');
            }
            if (!Schema::hasColumn('configuracaos', 'cor_fundo_campeonato')) {
                $table->string('cor_fundo_campeonato')->nullable()->after('cor_botoes_perfil');
            }
            // Cash Out (do REI BET)
            if (!Schema::hasColumn('configuracaos', 'cash_out_ativo')) {
                $table->boolean('cash_out_ativo')->default(false)->after('cor_fundo_campeonato');
            }
            if (!Schema::hasColumn('configuracaos', 'cash_out_taxa')) {
                $table->decimal('cash_out_taxa', 5, 2)->default(10.00)->after('cash_out_ativo');
            }
            // E-Sports toggle
            if (!Schema::hasColumn('configuracaos', 'op_e_sports')) {
                $table->string('op_e_sports')->nullable()->default('Não')->after('cash_out_taxa');
            }
        });

        // === CAMPOS REI BET PARA TABELA odds ===
        Schema::table('odds', function (Blueprint $table) {
            if (!Schema::hasColumn('odds', 'mercado_full_name')) {
                $table->string('mercado_full_name')->nullable()->after('value');
            }
            if (!Schema::hasColumn('odds', 'selectionId')) {
                $table->string('selectionId')->nullable()->after('mercado_full_name');
            }
            if (!Schema::hasColumn('odds', 'state')) {
                $table->string('state')->nullable()->after('selectionId');
            }
            if (!Schema::hasColumn('odds', 'order')) {
                $table->integer('order')->nullable()->after('state');
            }
            if (!Schema::hasColumn('odds', 'uuid')) {
                $table->string('uuid')->nullable()->after('order');
            }
            if (!Schema::hasColumn('odds', 'short_name')) {
                $table->string('short_name')->nullable()->after('uuid');
            }
            if (!Schema::hasColumn('odds', 'goals')) {
                $table->double('goals')->nullable()->after('short_name');
            }
        });

        // === CAMPOS REI BET PARA TABELA palpites ===
        Schema::table('palpites', function (Blueprint $table) {
            if (!Schema::hasColumn('palpites', 'score')) {
                $table->string('score')->nullable()->after('status');
            }
        });

        // === CAMPOS REI BET PARA TABELA apostas ===
        Schema::table('apostas', function (Blueprint $table) {
            if (!Schema::hasColumn('apostas', 'invoice_id')) {
                $table->string('invoice_id')->nullable()->after('cotacao');
            }
            if (!Schema::hasColumn('apostas', 'qr_code')) {
                $table->longText('qr_code')->nullable()->after('invoice_id');
            }
            if (!Schema::hasColumn('apostas', 'qr_code_text')) {
                $table->longText('qr_code_text')->nullable()->after('qr_code');
            }
            if (!Schema::hasColumn('apostas', 'adm_id')) {
                $table->bigInteger('adm_id')->nullable()->after('gerente_id');
            }
            if (!Schema::hasColumn('apostas', 'tipo_aposta')) {
                $table->string('tipo_aposta')->nullable()->after('modalidade');
            }
            if (!Schema::hasColumn('apostas', 'cupom')) {
                $table->string('cupom')->nullable()->after('tipo_aposta');
            }
            if (!Schema::hasColumn('apostas', 'retorno_cambista')) {
                $table->decimal('retorno_cambista', 15, 2)->default(0)->after('retorno_possivel');
            }
        });
    }

    public function down(): void
    {
        // Rollback não recomendado para adições de colunas
    }
};
