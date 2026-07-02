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
        Schema::create('loto_results', function (Blueprint $table) {
            $table->id();
            $table->string('concurso');
            $table->string('tipo'); // Quina, Mega-Sena
            $table->date('data_sorteio');
            $table->json('dezenas');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loto_results');
    }
};
