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
        // 1. SISTEMA DE BANCA (WHITE-LABEL)
        Schema::create('sites', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('complete_name')->nullable();
            $table->string('domain')->unique();
            $table->enum('status', ['active', 'suspended', 'pending'])->default('active');
            
            // LAYOUT & TEMAS
            $table->string('layout_theme', 50)->default('default');
            $table->string('primary_color', 20)->default('#1c3464');
            $table->string('secondary_color', 20)->default('#2a4b8d');
            $table->string('theme_color', 50)->default('verde-claro'); // Compatibilidade legada
            $table->text('logo_path')->nullable();
            $table->text('favicon_path')->nullable();
            
            // MÓDULOS ATIVOS
            $table->tinyInteger('seniha_enabled')->default(1);
            $table->tinyInteger('queniha_enabled')->default(1);
            $table->tinyInteger('loto_enabled')->default(1);
            $table->tinyInteger('bonus_enabled')->default(0);
            $table->tinyInteger('cashout_enabled')->default(1);
            
            // REGRAS DE NEGÓCIO E FINANCEIRO
            $table->decimal('due_value', 15, 2)->default(500.00);
            $table->integer('billing_day')->default(10);
            $table->longText('regulation')->nullable();
            $table->string('whatsapp_number', 20)->nullable();
            $table->string('whatsapp_token')->nullable();
            $table->string('pix_gateway', 50)->default('mercado_pago');
            $table->string('pix_client_id')->nullable();
            $table->string('pix_client_secret')->nullable();

            // LEGACY / EXTRA
            $table->text('texto_rodape_bilhete')->nullable();
            $table->string('social_instagram')->nullable();
            $table->tinyInteger('carrosel_ativado')->default(1);

            $table->timestamps();
        });

        // 2. CONFIGURAÇÕES DE APOSTAS TÁTICAS
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->onDelete('cascade');
            $table->decimal('min_bet_amount', 10, 2)->default(2.00);
            $table->decimal('max_bet_amount', 10, 2)->default(1000.00);
            $table->decimal('max_payout', 15, 2)->default(10000.00);
            $table->decimal('min_withdrawal', 10, 2)->default(50.00);
            $table->decimal('cashout_tax', 5, 2)->default(10.00);
            $table->integer('cashout_delay_seconds')->default(5);
            $table->timestamps();
        });

        // 3. REGIOES
        Schema::create('regions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->timestamps();
        });

        // 4. USUÁRIOS (MASTER)
        Schema::create('master_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('region_id')->nullable()->constrained('regions');
            $table->foreignId('gerente_id')->nullable();
            $table->string('name');
            $table->string('username')->unique();
            $table->string('email')->nullable();
            $table->string('password');
            $table->string('role')->default('client');
            $table->string('nivel')->nullable(); // Compatibilidade
            
            // COMISSÕES
            $table->decimal('comissao1', 5, 2)->default(0);
            $table->decimal('comissao2', 5, 2)->default(0);
            $table->decimal('comissao3', 5, 2)->default(0);
            $table->decimal('comissao4', 5, 2)->default(0);
            $table->decimal('comissao5', 5, 2)->default(0);
            $table->decimal('comissao6', 5, 2)->default(0);
            $table->decimal('comissao7', 5, 2)->default(0);
            $table->decimal('comissao8', 5, 2)->default(0);
            $table->decimal('comissao9', 5, 2)->default(0);
            $table->decimal('comissao10', 5, 2)->default(0);
            
            $table->decimal('balance', 15, 2)->default(0);
            $table->decimal('balance_bonus', 15, 2)->default(0);
            $table->tinyInteger('status')->default(1);
            $table->string('pix_key')->nullable();
            $table->timestamps();
        });

        // 5. EVENTOS MANUAIS (VAQUEJADA / X1)
        Schema::create('manual_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('manual_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('manual_categories');
            $table->foreignId('site_id')->constrained('sites');
            $table->string('title');
            $table->string('home_team')->nullable();
            $table->string('away_team')->nullable();
            $table->decimal('odd_home', 10, 2)->default(1.00);
            $table->decimal('odd_draw', 10, 2)->default(1.00);
            $table->decimal('odd_away', 10, 2)->default(1.00);
            $table->dateTime('start_time');
            $table->enum('status', ['open', 'finished', 'cancelled'])->default('open');
            $table->timestamps();
        });

        Schema::create('manual_markets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('manual_events')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('manual_odds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('market_id')->constrained('manual_markets')->onDelete('cascade');
            $table->string('label');
            $table->decimal('value', 10, 2);
            $table->tinyInteger('is_winner')->default(0);
            $table->timestamps();
        });

        // 6. BANNERS
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->onDelete('cascade');
            $table->string('image_path');
            $table->string('link')->nullable();
            $table->integer('order')->default(0);
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });

        // 7. DESTAQUES (Featured)
        Schema::create('featured_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->onDelete('cascade');
            $table->bigInteger('match_id');
            $table->string('home_team')->nullable();
            $table->string('away_team')->nullable();
            $table->dateTime('match_date')->nullable();
            $table->timestamps();
        });

        // 8. MOTOR DE APOSTAS 
        Schema::create('bets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained();
            $table->foreignId('user_id')->constrained('master_users');
            $table->string('external_code', 20)->unique()->nullable();
            $table->decimal('amount', 15, 2);
            $table->decimal('potential_payout', 15, 2);
            $table->enum('status', ['open', 'won', 'lost', 'cancelled', 'cashed_out'])->default('open');
            $table->timestamps();
        });

        Schema::create('bet_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bet_id')->constrained()->onDelete('cascade');
            $table->bigInteger('match_id');
            $table->string('home_team')->nullable();
            $table->string('away_team')->nullable();
            $table->decimal('selection_odd', 10, 2);
            $table->enum('status', ['pending', 'won', 'lost', 'cancelled'])->default('pending');
            $table->timestamps();
        });

        Schema::create('site_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->onDelete('cascade');
            $table->enum('page_type', ['regulamento', 'sobre_nos', 'compartilhamentos', 'termos']);
            $table->string('title')->nullable();
            $table->longText('content')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_pages');
        Schema::dropIfExists('bet_items');
        Schema::dropIfExists('bets');
        Schema::dropIfExists('featured_matches');
        Schema::dropIfExists('banners');
        Schema::dropIfExists('manual_odds');
        Schema::dropIfExists('manual_markets');
        Schema::dropIfExists('manual_events');
        Schema::dropIfExists('manual_categories');
        Schema::dropIfExists('master_users');
        Schema::dropIfExists('regions');
        Schema::dropIfExists('site_settings');
        Schema::dropIfExists('sites');
    }
};
