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
            // Se as colunas não existirem, adiciona
            if (!Schema::hasColumn('site_settings', 'allow_mixed_bets')) {
                $table->boolean('allow_mixed_bets')->default(true);
            }
            
            // Integrações
            if (!Schema::hasColumn('site_settings', 'google_analytics_enabled')) {
                $table->boolean('google_analytics_enabled')->default(false);
            }
            if (!Schema::hasColumn('site_settings', 'google_analytics_script')) {
                $table->text('google_analytics_script')->nullable();
            }
            if (!Schema::hasColumn('site_settings', 'meta_pixel_enabled')) {
                $table->boolean('meta_pixel_enabled')->default(false);
            }
            if (!Schema::hasColumn('site_settings', 'meta_pixel_id')) {
                $table->string('meta_pixel_id')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn([
                'allow_mixed_bets',
                'google_analytics_enabled', 'google_analytics_script',
                'meta_pixel_enabled', 'meta_pixel_id'
            ]);
        });
    }
};
