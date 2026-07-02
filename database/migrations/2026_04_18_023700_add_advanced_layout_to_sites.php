<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            if (!Schema::hasColumn('sites', 'active_custom_colors')) $table->boolean('active_custom_colors')->default(false);
            if (!Schema::hasColumn('sites', 'sidebar_color')) $table->string('sidebar_color', 20)->nullable();
            if (!Schema::hasColumn('sites', 'game_container_color')) $table->string('game_container_color', 20)->nullable();
            if (!Schema::hasColumn('sites', 'logo_container_color')) $table->string('logo_container_color', 20)->nullable();
            if (!Schema::hasColumn('sites', 'odds_button_color')) $table->string('odds_button_color', 20)->nullable();
            if (!Schema::hasColumn('sites', 'bet_button_color')) $table->string('bet_button_color', 20)->nullable();
            if (!Schema::hasColumn('sites', 'background_color')) $table->string('background_color', 20)->nullable();
        });
    }

    public function down(): void
    {
    }
};
