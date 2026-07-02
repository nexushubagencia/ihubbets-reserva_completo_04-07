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
        if (!Schema::hasTable('apostas')) {
            Schema::create('apostas', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('site_id')->index();
                $table->unsignedBigInteger('user_id')->nullable()->index(); // Cambista
                $table->unsignedBigInteger('gerente_id')->nullable()->index();
                $table->string('tipo', 50)->default('Simples'); // Simples, Casadinha, Bolão
                $table->string('modalidade', 50)->default('Futebol');
                $table->decimal('valor_apostado', 15, 2)->default(0);
                $table->decimal('retorno_possivel', 15, 2)->default(0);
                $table->decimal('comicao', 15, 2)->default(0);
                $table->string('status', 50)->default('Aberto');
                $table->string('codigo_bilhete', 20)->unique()->nullable();
                $table->integer('total_palpites')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('palpites')) {
            Schema::create('palpites', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('aposta_id')->index();
                $table->unsignedBigInteger('match_id')->nullable();
                $table->string('home_team')->nullable();
                $table->string('away_team')->nullable();
                $table->string('market_name')->nullable();
                $table->string('selection_label')->nullable();
                $table->decimal('selection_odd', 10, 2)->default(1);
                $table->string('status', 50)->default('Aberto');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('palpites');
        Schema::dropIfExists('apostas');
    }
};
