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
            $table->decimal('comissao_loto', 5, 2)->default(0)->nullable();
            $table->decimal('saldo_simples', 15, 2)->default(0)->nullable();
            $table->decimal('saldo_casadinha', 15, 2)->default(0)->nullable();
            $table->decimal('saldo_loto', 15, 2)->default(0)->nullable();
            $table->decimal('saldo_gerente', 15, 2)->default(0)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_users', function (Blueprint $table) {
            $table->dropColumn(['comissao_loto', 'saldo_simples', 'saldo_casadinha', 'saldo_loto', 'saldo_gerente']);
        });
    }
};
