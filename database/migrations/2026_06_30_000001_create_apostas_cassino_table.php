<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('apostas_cassino', function (Blueprint $table) {
            $table->id();
            $table->string('bet_id');
            $table->foreignId('user_id')->constrained('master_users')->onDelete('cascade');
            $table->unsignedInteger('game_id');
            $table->decimal('bet', 12, 2);
            $table->decimal('win', 12, 2);
            $table->string('bet_info')->nullable();
            $table->foreignId('site_id')->constrained('sites')->onDelete('cascade');
            $table->timestamps();

            $table->index(['site_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('apostas_cassino');
    }
};
