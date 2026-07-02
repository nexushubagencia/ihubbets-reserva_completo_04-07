<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('afer_tomorow_match_flashes', function (Blueprint $table) {
            $table->id();
            $table->json('dados')->nullable();
            $table->unsignedBigInteger('site_id')->default(1);
            $table->integer('sport_id')->nullable();
            $table->timestamps();

            $table->index('site_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('afer_tomorow_match_flashes');
    }
};
