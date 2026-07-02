<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('traducoes', function (Blueprint $table) {
            $table->id();
            $table->string('tipo');
            $table->string('texto_original')->index();
            $table->string('texto_traduzido');
            $table->foreignId('site_id')->constrained('sites')->onDelete('cascade');
            $table->timestamps();

            $table->index(['site_id', 'tipo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('traducoes');
    }
};
