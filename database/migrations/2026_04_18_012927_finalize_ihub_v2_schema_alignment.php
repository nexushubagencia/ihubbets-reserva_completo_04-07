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
        // 1. SISTEMA DE CARTEIRAS (Wallets)
        if (!Schema::hasTable('wallets')) {
            Schema::create('wallets', function (Blueprint $table) {
                $table->id();
                $table->foreignId('site_id')->constrained()->onDelete('cascade');
                $table->foreignId('user_id')->unique()->constrained('master_users')->onDelete('cascade');
                $table->decimal('balance_real', 15, 2)->default(0);
                $table->decimal('balance_bonus', 15, 2)->default(0);
                $table->timestamps();
            });
        }

        // 2. EXTRATO FINANCEIRO (Transactions)
        if (!Schema::hasTable('transactions')) {
            Schema::create('transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('site_id')->constrained()->onDelete('cascade');
                $table->foreignId('user_id')->constrained('master_users')->onDelete('cascade');
                $table->string('type'); // bet_place, bet_payout, deposit, withdraw, commission, adjustment
                $table->decimal('amount', 15, 2);
                $table->string('gateway_ref')->nullable();
                $table->enum('status', ['pending', 'completed', 'cancelled', 'failed'])->default('completed');
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

        // 3. LOGS E NOTIFICAÇÕES
        if (!Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table) {
                $table->id();
                $table->foreignId('site_id')->constrained()->onDelete('cascade');
                $table->foreignId('user_id')->nullable(); // null = global admin
                $table->string('title');
                $table->text('message');
                $table->string('type')->default('info');
                $table->boolean('is_read')->default(false);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('audit_logs')) {
            Schema::create('audit_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('site_id')->constrained()->onDelete('cascade');
                $table->foreignId('user_id')->nullable();
                $table->string('action');
                $table->string('target_type')->nullable();
                $table->string('target_id')->nullable();
                $table->json('old_values')->nullable();
                $table->json('new_values')->nullable();
                $table->string('ip_address')->nullable();
                $table->timestamps();
            });
        }

        // 4. EXPANSÃO DE SITES E CONFIGURAÇÕES
        Schema::table('sites', function (Blueprint $table) {
            if (!Schema::hasColumn('sites', 'billing_status')) {
                $table->enum('billing_status', ['paid', 'pending', 'overdue'])->default('paid')->after('status');
            }
            if (!Schema::hasColumn('sites', 'next_due_date')) {
                $table->date('next_due_date')->nullable()->after('billing_day');
            }
            if (!Schema::hasColumn('sites', 'facebook_pixel_id')) {
                $table->string('facebook_pixel_id')->nullable();
            }
            if (!Schema::hasColumn('sites', 'facebook_access_token')) {
                $table->text('facebook_access_token')->nullable();
            }
            if (!Schema::hasColumn('sites', 'active_custom_colors')) { $table->boolean('active_custom_colors')->default(false); }
            if (!Schema::hasColumn('sites', 'custom_colors')) { $table->json('custom_colors')->nullable(); }
        });

        Schema::table('site_settings', function (Blueprint $table) {
            // Travas do BetValidator
            $newCols = [
                'aposta_ativa' => ['type' => 'boolean', 'default' => true],
                'bloq_aposta_madrugada' => ['type' => 'boolean', 'default' => true],
                'bloquear_odd_abaixo' => ['type' => 'decimal', 'default' => 1.00],
                'travar_odd_acima' => ['type' => 'decimal', 'default' => 1000.00],
                'data_limite_jogos' => ['type' => 'date', 'default' => '2050-12-31'],
                'hours_limit_date' => ['type' => 'time', 'default' => '23:59:59'],
                'limite_apostas_iguais' => ['type' => 'integer', 'default' => 0],
                'alerta_aposta_acima' => ['type' => 'decimal', 'default' => 500.00],
                'cotacao_mini_bilhete_mult' => ['type' => 'decimal', 'default' => 1.40],
                'live_valor_mini_aposta' => ['type' => 'decimal', 'default' => 2.00],
                'live_valor_max_aposta' => ['type' => 'decimal', 'default' => 500.00],
                'live_premio_max' => ['type' => 'decimal', 'default' => 10000.00],
                'live_cotacao_mini_bilhete' => ['type' => 'decimal', 'default' => 2.00],
                'quantidade_jogos_mini_bilhete' => ['type' => 'integer', 'default' => 1],
                'quantidade_jogos_max_bilhete' => ['type' => 'integer', 'default' => 25],
                'live_quantidade_jogos_mini_bilhete' => ['type' => 'integer', 'default' => 1],
                'live_quantidade_jogos_max_bilhete' => ['type' => 'integer', 'default' => 15],
            ];

            foreach ($newCols as $col => $attr) {
                if (!Schema::hasColumn('site_settings', $col)) {
                    if ($attr['type'] == 'decimal') $table->decimal($col, 15, 2)->default($attr['default']);
                    elseif ($attr['type'] == 'boolean') $table->boolean($col)->default($attr['default']);
                    elseif ($attr['type'] == 'integer') $table->integer($col)->default($attr['default']);
                    elseif ($attr['type'] == 'date') $table->date($col)->default($attr['default']);
                    elseif ($attr['type'] == 'time') $table->time($col)->default($attr['default']);
                }
            }
        });

        // 5. AJUSTES EM BETS E USERS
        Schema::table('bets', function (Blueprint $table) {
            if (!Schema::hasColumn('bets', 'ticket_signature')) { $table->string('ticket_signature')->nullable()->index(); }
            if (!Schema::hasColumn('bets', 'manager_id')) { $table->unsignedBigInteger('manager_id')->nullable()->after('user_id'); }
            if (!Schema::hasColumn('bets', 'commission_percent')) { $table->decimal('commission_percent', 5, 2)->default(0); }
            if (!Schema::hasColumn('bets', 'commission_amount')) { $table->decimal('commission_amount', 15, 2)->default(0); }
        });

        Schema::table('master_users', function (Blueprint $table) {
            if (!Schema::hasColumn('master_users', 'manager_commission_rate')) {
                $table->decimal('manager_commission_rate', 5, 2)->default(0);
            }
            if (!Schema::hasColumn('master_users', 'can_cancel_tickets')) {
                $table->boolean('can_cancel_tickets')->default(false);
            }
        });
        
        Schema::table('bet_items', function (Blueprint $table) {
            if (!Schema::hasColumn('bet_items', 'league_name')) { $table->string('league_name')->nullable(); }
            if (!Schema::hasColumn('bet_items', 'market_name')) { $table->string('market_name')->nullable(); }
            if (!Schema::hasColumn('bet_items', 'selection_label')) { $table->string('selection_label')->nullable(); }
        });

        Schema::table('featured_matches', function (Blueprint $table) {
            if (!Schema::hasColumn('featured_matches', 'sport')) { $table->string('sport', 50)->default('soccer'); }
            if (!Schema::hasColumn('featured_matches', 'league_name')) { $table->string('league_name')->nullable(); }
            if (!Schema::hasColumn('featured_matches', 'order')) { $table->integer('order')->default(0); }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Geralmente não removemos em auditoria cirúrgica para evitar perda de dados
    }
};
