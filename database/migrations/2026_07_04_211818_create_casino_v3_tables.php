<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('casino_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('casino_providers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable();
            $table->string('name');
            $table->string('distribution')->nullable();
            $table->decimal('rtp', 8, 2)->nullable();
            $table->integer('views')->default(0);
            $table->boolean('status')->default(1);
            $table->timestamps();
        });

        Schema::create('casino_games', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->nullable()->constrained('casino_providers')->nullOnDelete();
            $table->string('game_server_url')->nullable();
            $table->string('game_id')->nullable();
            $table->string('game_id_maxapi')->nullable();
            $table->string('game_name');
            $table->string('game_code');
            $table->string('game_type')->nullable();
            $table->text('description')->nullable();
            $table->string('cover')->nullable();
            $table->boolean('status')->default(1);
            $table->string('technology')->nullable();
            $table->boolean('has_lobby')->default(0);
            $table->boolean('is_mobile')->default(1);
            $table->boolean('has_freespins')->default(0);
            $table->boolean('has_tables')->default(0);
            $table->boolean('only_demo')->default(0);
            $table->decimal('rtp', 8, 2)->nullable();
            $table->string('distribution')->nullable();
            $table->integer('views')->default(0);
            $table->boolean('is_featured')->default(0);
            $table->boolean('show_home')->default(1);
            $table->timestamps();

            $table->index(['provider_id', 'status']);
            $table->index(['distribution', 'status']);
            $table->index(['game_code']);
            $table->index(['game_type', 'status']);
        });

        Schema::create('casino_category_game', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained('casino_games')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('casino_categories')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['game_id', 'category_id']);
        });

        Schema::create('casino_games_keys', function (Blueprint $table) {
            $table->id();
            $table->string('merchant_url')->nullable();
            $table->string('merchant_id')->nullable();
            $table->string('merchant_key')->nullable();

            $table->string('venix_agent_code')->nullable();
            $table->string('venix_agent_token')->nullable();
            $table->string('venix_agent_secret')->nullable();

            $table->string('pig_agent_code')->nullable();
            $table->string('pig_agent_token')->nullable();
            $table->string('pig_agent_secret')->nullable();

            $table->string('play_gaming_hall')->nullable();
            $table->string('play_gaming_key')->nullable();
            $table->string('play_gaming_login')->nullable();

            $table->string('games2_agent_code')->nullable();
            $table->string('games2_agent_token')->nullable();
            $table->string('games2_agent_secret_key')->nullable();
            $table->string('games2_api_endpoint')->nullable();

            $table->string('evergame_agent_code')->nullable();
            $table->string('evergame_agent_token')->nullable();
            $table->string('evergame_api_endpoint')->nullable();

            $table->string('worldslot_agent_code')->nullable();
            $table->string('worldslot_agent_token')->nullable();
            $table->string('worldslot_agent_secret_key')->nullable();
            $table->string('worldslot_api_endpoint')->nullable();

            $table->string('agent_code')->nullable();
            $table->string('agent_token')->nullable();
            $table->string('agent_secret_key')->nullable();
            $table->string('api_endpoint')->nullable();

            $table->string('salsa_base_uri')->nullable();
            $table->string('salsa_pn')->nullable();
            $table->string('salsa_key')->nullable();

            $table->string('vibra_site_id')->nullable();
            $table->string('vibra_game_mode')->nullable();

            $table->string('maxapigames_agent_code')->nullable();
            $table->string('maxapigames_agent_token')->nullable();
            $table->string('maxapigames_agent_secret')->nullable();

            $table->timestamps();
        });

        Schema::create('casino_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('master_users')->cascadeOnDelete();
            $table->string('session_id')->nullable();
            $table->string('transaction_id')->nullable()->index();
            $table->string('game')->nullable();
            $table->string('game_uuid')->nullable();
            $table->string('type')->nullable();
            $table->string('type_money')->nullable();
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('providers')->nullable();
            $table->boolean('refunded')->default(0);
            $table->string('round_id')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['type', 'created_at']);
        });

        Schema::create('casino_game_favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('master_users')->cascadeOnDelete();
            $table->foreignId('game_id')->constrained('casino_games')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'game_id']);
        });

        Schema::create('casino_game_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('master_users')->cascadeOnDelete();
            $table->foreignId('game_id')->constrained('casino_games')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'game_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('casino_game_likes');
        Schema::dropIfExists('casino_game_favorites');
        Schema::dropIfExists('casino_orders');
        Schema::dropIfExists('casino_games_keys');
        Schema::dropIfExists('casino_category_game');
        Schema::dropIfExists('casino_games');
        Schema::dropIfExists('casino_providers');
        Schema::dropIfExists('casino_categories');
    }
};
