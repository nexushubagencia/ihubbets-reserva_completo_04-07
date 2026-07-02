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
        Schema::create('palpite_lotos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('aposta_id');
            $table->string('concurso')->nullable();
            $table->string('tipo'); // Quininha, Seninha
            $table->string('dezena');
            $table->string('status')->default('Aberto');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('palpite_lotos');
    }
};
