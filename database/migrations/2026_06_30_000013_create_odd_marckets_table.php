<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('odd_marckets', function (Blueprint $table) {
            $table->id();
            $table->string('mercado');
            $table->string('odd');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('odd_marckets');
    }
};
