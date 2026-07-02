<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('palpite_bolao', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('aposta_id');
            $table->unsignedBigInteger('rodada_id');
            $table->unsignedBigInteger('match_id');
            $table->string('home');
            $table->string('away');
            $table->string('mercado')->default('Resultado Final');
            $table->string('palpite'); // 1, X, 2
            $table->string('status')->default('Aberto');
            $table->string('resultado')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('palpite_bolao');
    }
};
