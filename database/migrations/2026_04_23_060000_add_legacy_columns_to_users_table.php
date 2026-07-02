<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adiciona TODAS as colunas do sistema antigo na tabela users
     * para compatibilidade total com o nexus-clone v.1
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Campos de autenticação e identidade
            if (!Schema::hasColumn('users', 'username')) {
                $table->string('username', 100)->nullable()->unique()->after('name');
            }
            if (!Schema::hasColumn('users', 'role')) {
                $table->string('role', 30)->default('user')->after('password');
            }
            if (!Schema::hasColumn('users', 'nivel')) {
                $table->string('nivel', 30)->nullable()->after('role');
            }
            if (!Schema::hasColumn('users', 'situacao')) {
                $table->string('situacao', 20)->default('Ativo')->after('nivel');
            }

            // Multi-Tenant
            if (!Schema::hasColumn('users', 'site_id')) {
                $table->string('site_id', 50)->default('ihub')->after('situacao');
            }

            // Hierarquia
            if (!Schema::hasColumn('users', 'adm_id')) {
                $table->unsignedBigInteger('adm_id')->nullable()->after('site_id');
            }
            if (!Schema::hasColumn('users', 'gerente_id')) {
                $table->unsignedBigInteger('gerente_id')->nullable()->after('adm_id');
            }
            if (!Schema::hasColumn('users', 'parent_id')) {
                $table->unsignedBigInteger('parent_id')->nullable()->after('gerente_id');
            }

            // Contato
            if (!Schema::hasColumn('users', 'contato')) {
                $table->string('contato', 100)->nullable();
            }
            if (!Schema::hasColumn('users', 'endereco')) {
                $table->string('endereco', 255)->nullable();
            }
            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone', 30)->nullable();
            }
            if (!Schema::hasColumn('users', 'cpf')) {
                $table->string('cpf', 20)->nullable();
            }

            // PIX
            if (!Schema::hasColumn('users', 'pix_key')) {
                $table->string('pix_key', 100)->nullable();
            }
            if (!Schema::hasColumn('users', 'pix_key_type')) {
                $table->string('pix_key_type', 20)->nullable();
            }

            // Comissões (sistema antigo - 10 faixas)
            for ($i = 1; $i <= 10; $i++) {
                $col = "comissao{$i}";
                if (!Schema::hasColumn('users', $col)) {
                    $table->decimal($col, 8, 2)->default(0);
                }
            }

            if (!Schema::hasColumn('users', 'comissao_gerente')) {
                $table->decimal('comissao_gerente', 8, 2)->default(0);
            }
            if (!Schema::hasColumn('users', 'comissao_cambistas')) {
                $table->decimal('comissao_cambistas', 8, 2)->default(0);
            }
            if (!Schema::hasColumn('users', 'comissao_loto')) {
                $table->decimal('comissao_loto', 8, 2)->default(0);
            }
            if (!Schema::hasColumn('users', 'commission_rate')) {
                $table->decimal('commission_rate', 8, 2)->default(0);
            }

            // Saldos
            if (!Schema::hasColumn('users', 'saldo_casadinha')) {
                $table->decimal('saldo_casadinha', 15, 2)->default(0);
            }
            if (!Schema::hasColumn('users', 'saldo_loto')) {
                $table->decimal('saldo_loto', 15, 2)->default(0);
            }
            if (!Schema::hasColumn('users', 'saldo_simples')) {
                $table->decimal('saldo_simples', 15, 2)->default(0);
            }
            if (!Schema::hasColumn('users', 'saldo_gerente')) {
                $table->decimal('saldo_gerente', 15, 2)->default(0);
            }
            if (!Schema::hasColumn('users', 'balance')) {
                $table->decimal('balance', 15, 2)->default(0);
            }

            // Financeiro
            if (!Schema::hasColumn('users', 'entradas')) {
                $table->decimal('entradas', 15, 2)->default(0);
            }
            if (!Schema::hasColumn('users', 'entrada_loto')) {
                $table->decimal('entrada_loto', 15, 2)->default(0);
            }
            if (!Schema::hasColumn('users', 'entrada_casadinha')) {
                $table->decimal('entrada_casadinha', 15, 2)->default(0);
            }
            if (!Schema::hasColumn('users', 'entrada_simples')) {
                $table->decimal('entrada_simples', 15, 2)->default(0);
            }
            if (!Schema::hasColumn('users', 'entradas_abertas')) {
                $table->decimal('entradas_abertas', 15, 2)->default(0);
            }
            if (!Schema::hasColumn('users', 'saidas')) {
                $table->decimal('saidas', 15, 2)->default(0);
            }
            if (!Schema::hasColumn('users', 'comissoes')) {
                $table->decimal('comissoes', 15, 2)->default(0);
            }
            if (!Schema::hasColumn('users', 'lancamentos')) {
                $table->decimal('lancamentos', 15, 2)->default(0);
            }
            if (!Schema::hasColumn('users', 'quantidade_aposta')) {
                $table->integer('quantidade_aposta')->default(0);
            }

            // Status/Avatar
            if (!Schema::hasColumn('users', 'avatar')) {
                $table->string('avatar', 255)->nullable();
            }
            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true);
            }

            // Quantidade (campo antigo)
            if (!Schema::hasColumn('users', 'quantidade')) {
                $table->integer('quantidade')->default(0);
            }

            // Indexes
            $table->index('site_id');
            $table->index('nivel');
            $table->index('gerente_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = [
                'username', 'role', 'nivel', 'situacao', 'site_id',
                'adm_id', 'gerente_id', 'parent_id',
                'contato', 'endereco', 'phone', 'cpf',
                'pix_key', 'pix_key_type',
                'comissao1', 'comissao2', 'comissao3', 'comissao4', 'comissao5',
                'comissao6', 'comissao7', 'comissao8', 'comissao9', 'comissao10',
                'comissao_gerente', 'comissao_cambistas', 'comissao_loto', 'commission_rate',
                'saldo_casadinha', 'saldo_loto', 'saldo_simples', 'saldo_gerente', 'balance',
                'entradas', 'entrada_loto', 'entrada_casadinha', 'entrada_simples',
                'entradas_abertas', 'saidas', 'comissoes', 'lancamentos',
                'quantidade_aposta', 'avatar', 'is_active', 'quantidade',
            ];
            $table->dropColumn($columns);
        });
    }
};
