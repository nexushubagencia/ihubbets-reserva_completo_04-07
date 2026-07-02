<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('apifootball_leagues', function (Blueprint $table) {
            $table->id();
            $table->integer('league_id')->unique();
            $table->string('name');
            $table->string('country');
            $table->string('logo')->nullable();
            $table->integer('season');
            $table->string('sport')->default('football');
            $table->boolean('active')->default(false);
            $table->foreignId('site_id')->constrained('sites')->onDelete('cascade');
            $table->timestamps();

            $table->index(['site_id', 'active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('apifootball_leagues');
    }
};
