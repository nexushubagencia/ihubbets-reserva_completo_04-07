<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('master_users', function (Blueprint $table) {
            $table->decimal('entradas', 15, 2)->default(0)->after('balance');
            $table->decimal('saidas', 15, 2)->default(0)->after('entradas');
            $table->decimal('comissoes', 15, 2)->default(0)->after('saidas');
            $table->decimal('lancamentos', 15, 2)->default(0)->after('comissoes');
            $table->integer('quantidade_aposta')->default(0)->after('lancamentos');
            $table->decimal('entrada_loto', 15, 2)->default(0)->after('quantidade_aposta');
            $table->decimal('entrada_casadinha', 15, 2)->default(0)->after('entrada_loto');
            $table->decimal('entrada_simples', 15, 2)->default(0)->after('entrada_casadinha');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_users', function (Blueprint $table) {
            $table->dropColumn([
                'entradas', 'saidas', 'comissoes', 'lancamentos', 
                'quantidade_aposta', 'entrada_loto', 'entrada_casadinha', 'entrada_simples'
            ]);
        });
    }
};
