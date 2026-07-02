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
        // 1. TABELA DE PARTIDAS (MATCHS)
        if (!Schema::hasTable('matchs')) {
            Schema::create('matchs', function (Blueprint $table) {
                $table->id();
                $table->bigInteger('event_id')->index();
                $table->bigInteger('our_event_id')->nullable();
                $table->integer('sport_id')->default(1); // 1 = Futebol
                $table->string('sport_name')->default('Futebol');
                
                $table->bigInteger('league_id')->index();
                $table->string('league_cc', 10)->nullable();
                $table->string('league');
                
                $table->string('home');
                $table->string('away');
                $table->string('home_true')->nullable(); // Nome original/traduzido
                $table->string('away_true')->nullable();
                
                $table->string('image_id_home')->nullable();
                $table->string('image_id_away')->nullable();
                
                $table->string('score')->nullable();
                $table->integer('time_status')->default(0); // 0=Not Start, 1=InPlay, 2=Ended
                $table->bigInteger('time')->nullable(); // Local betsapi timestamp
                $table->dateTime('date')->index();
                
                $table->string('confronto')->nullable(); // Slug de busca
                $table->string('visible', 10)->default('Sim');
                $table->integer('order')->default(0);
                $table->integer('schedule')->default(0);
                
                // STATUS DETALHADO (Ao vivo)
                $table->string('live_status')->nullable();
                $table->string('halfTimeScoreHome')->nullable();
                $table->string('halfTimeScoreAway')->nullable();
                $table->string('fullTimeScoreHome')->nullable();
                $table->string('fullTimeScoreAway')->nullable();
                $table->integer('numberOfCornersHome')->nullable();
                $table->integer('numberOfCornersAway')->nullable();
                $table->integer('numberOfYellowCardsHome')->nullable();
                $table->integer('numberOfYellowCardsAway')->nullable();
                $table->integer('numberOfRedCardsHome')->nullable();
                $table->integer('numberOfRedCardsAway')->nullable();

                $table->timestamps();
            });
        }

        // 2. TABELA DE ODDS
        if (!Schema::hasTable('odds')) {
            Schema::create('odds', function (Blueprint $table) {
                $table->id();
                $table->bigInteger('event_id')->index(); // FK com matchs
                $table->string('market_name')->nullable();
                $table->string('label');
                $table->decimal('value', 10, 2);
                $table->string('type')->nullable(); // generic field for categorization
                $table->timestamps();
            });
        }

        // 3. TABELA DE LIGAS PRINCIPAIS (Para gestão administrativa)
        if (!Schema::hasTable('main_leagues')) {
            Schema::create('main_leagues', function (Blueprint $table) {
                $table->id();
                $table->foreignId('site_id')->constrained()->onDelete('cascade');
                $table->bigInteger('league_id');
                $table->string('league');
                $table->string('sport')->default('Futebol');
                $table->integer('order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // 4. TABELAS DE BLOQUEIO (MULTI-TENANT)
        if (!Schema::hasTable('block_leagues')) {
            Schema::create('block_leagues', function (Blueprint $table) {
                $table->id();
                $table->foreignId('site_id')->constrained()->onDelete('cascade');
                $table->string('league'); // Nome ou ID dependendo do legado
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('block_matchs')) {
            Schema::create('block_matchs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('site_id')->constrained()->onDelete('cascade');
                $table->bigInteger('event_id');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('block_matchs');
        Schema::dropIfExists('block_leagues');
        Schema::dropIfExists('main_leagues');
        Schema::dropIfExists('odds');
        Schema::dropIfExists('matchs');
    }
};
