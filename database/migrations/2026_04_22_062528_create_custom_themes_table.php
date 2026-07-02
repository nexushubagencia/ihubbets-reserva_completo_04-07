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
        Schema::create('custom_themes', function (Blueprint $table) {
            $table->id();
            $table->integer('site_id')->default(1);
            $table->string('name');
            $table->json('colors'); // Armazena as 12 variáveis de cor
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_themes');
    }
};
