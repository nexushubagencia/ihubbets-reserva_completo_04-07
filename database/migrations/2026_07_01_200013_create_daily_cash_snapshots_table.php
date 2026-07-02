<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_cash_snapshots', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('site_id')->default(1);
            $table->date('snapshot_date');
            $table->decimal('entradas_dia', 12, 2)->default(0);
            $table->decimal('saidas_dia', 12, 2)->default(0);
            $table->decimal('comissoes_dia', 12, 2)->default(0);
            $table->decimal('lancamentos_dia', 12, 2)->default(0);
            $table->integer('apostas_dia')->default(0);
            $table->decimal('lucro_dia', 12, 2)->default(0);
            $table->decimal('saldo_fechamento', 12, 2)->default(0);
            $table->timestamps();
            $table->unique(['user_id', 'snapshot_date', 'site_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_cash_snapshots');
    }
};
