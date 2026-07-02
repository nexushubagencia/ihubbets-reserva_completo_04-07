<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rodadas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('site_id')->default(1);
            $table->string('nome');
            $table->string('status')->default('Aberta'); // Aberta, Fechada, Finalizada
            $table->decimal('premio_max', 15, 2)->default(0);
            $table->decimal('premio_primeiro', 15, 2)->default(0);
            $table->decimal('premio_segundo', 15, 2)->default(0);
            $table->decimal('premio_terceiro', 15, 2)->default(0);
            $table->integer('quantidade')->default(0);
            $table->decimal('arrecadado', 15, 2)->default(0);
            $table->timestamp('data_fechamento')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rodadas');
    }
};
