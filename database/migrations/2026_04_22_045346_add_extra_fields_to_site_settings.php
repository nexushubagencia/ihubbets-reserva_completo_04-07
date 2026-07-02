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
        Schema::table('site_settings', function (Blueprint $table) {
            // Geral
            $table->string('site_language')->default('pt_BR')->nullable();
            $table->boolean('language_selector_enabled')->default(false);
            
            // Alertas
            $table->string('alert_email')->nullable();
            
            // Permissões
            $table->boolean('sellers_can_cancel')->default(true);
            $table->integer('cancellation_time_limit')->default(5);
            $table->boolean('manager_can_cancel')->default(true);
            $table->boolean('manager_can_create_sellers')->default(true);
            $table->boolean('manager_can_remove_sellers')->default(true);
            $table->boolean('manager_can_edit_sellers')->default(true);
            $table->boolean('validate_pin_once')->default(false);
            $table->boolean('reduce_increase_odds_pin')->default(false);
            
            // Layout
            $table->boolean('carousel_banners')->default(true);
            $table->string('whatsapp_number')->nullable();
            $table->string('whatsapp_support_link')->nullable();
            $table->string('facebook_link')->nullable();
            $table->string('youtube_link')->nullable();
            $table->string('instagram_link')->nullable();
            $table->text('footer_text')->nullable();
            $table->string('theme_name')->default('verde_claro');
            $table->boolean('custom_colors_enabled')->default(false);
            $table->string('sidebar_color')->default('#000000')->nullable();
            $table->string('game_container_color')->default('#ffffff')->nullable();
            $table->string('logo_container_color')->default('#ffffff')->nullable();
            $table->string('button_odds_color')->default('#007bff')->nullable();
            $table->string('button_home_draw_away_color')->default('#28a745')->nullable();
            $table->string('background_color')->default('#f4f6f9')->nullable();
            $table->string('lines_color')->default('#dee2e6')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn([
                'site_language', 'language_selector_enabled', 'alert_email',
                'sellers_can_cancel', 'cancellation_time_limit', 'manager_can_cancel',
                'manager_can_create_sellers', 'manager_can_remove_sellers', 'manager_can_edit_sellers',
                'validate_pin_once', 'reduce_increase_odds_pin', 'carousel_banners',
                'whatsapp_number', 'whatsapp_support_link', 'facebook_link', 'youtube_link',
                'instagram_link', 'footer_text', 'theme_name', 'custom_colors_enabled',
                'sidebar_color', 'game_container_color', 'logo_container_color',
                'button_odds_color', 'button_home_draw_away_color', 'background_color', 'lines_color'
            ]);
        });
    }
};
