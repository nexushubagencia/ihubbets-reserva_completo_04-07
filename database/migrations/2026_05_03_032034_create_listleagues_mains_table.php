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
        Schema::create('listleagues_mains', function (Blueprint $table) {
            $table->id();
            $table->string('sport')->nullable();
            $table->string('league_id')->nullable();
            $table->string('league')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('listleagues_mains');
    }
};
