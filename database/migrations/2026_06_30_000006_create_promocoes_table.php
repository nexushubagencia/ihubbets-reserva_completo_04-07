<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promocoes', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('tipo');
            $table->decimal('porcentagem', 8, 2)->default(0);
            $table->decimal('valor_maximo', 10, 2)->default(0);
            $table->decimal('rollover_multiplicador', 8, 2)->default(1);
            $table->boolean('status')->default(true);
            $table->foreignId('site_id')->constrained('sites')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promocoes');
    }
};
