<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cashier_closeouts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('closed_by');
            $table->unsignedBigInteger('site_id')->default(1);
            $table->string('turno')->default('integral');
            $table->decimal('total_entradas', 12, 2)->default(0);
            $table->decimal('total_saidas', 12, 2)->default(0);
            $table->decimal('total_comissoes', 12, 2)->default(0);
            $table->decimal('total_lancamentos', 12, 2)->default(0);
            $table->decimal('total_entradas_abertas', 12, 2)->default(0);
            $table->integer('quantidade_apostas')->default(0);
            $table->decimal('total_liquido', 12, 2)->default(0);
            $table->decimal('comissao_gerente', 12, 2)->default(0);
            $table->decimal('saldo_anterior', 12, 2)->default(0);
            $table->decimal('saldo_final', 12, 2)->default(0);
            $table->json('detalhes')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'site_id']);
            $table->index(['site_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cashier_closeouts');
    }
};
