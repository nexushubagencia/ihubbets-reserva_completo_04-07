<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('playfiver_games', function (Blueprint $table) {
            $table->id();
            $table->string('game_code')->unique();
            $table->string('name');
            $table->string('image_url')->nullable();
            $table->string('provider');
            $table->boolean('status')->default(true);
            $table->boolean('original')->default(false);
            $table->boolean('is_popular')->default(false);
            $table->foreignId('site_id')->constrained('sites')->onDelete('cascade');
            $table->timestamps();

            $table->index(['site_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('playfiver_games');
    }
};
