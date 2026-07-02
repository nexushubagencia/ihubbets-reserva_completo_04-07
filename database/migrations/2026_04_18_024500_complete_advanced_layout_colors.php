<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            if (!Schema::hasColumn('sites', 'odds_plus_button_color')) $table->string('odds_plus_button_color', 20)->nullable();
            if (!Schema::hasColumn('sites', 'bet_main_buttons_color')) $table->string('bet_main_buttons_color', 20)->nullable();
            if (!Schema::hasColumn('sites', 'border_color')) $table->string('border_color', 20)->nullable();
            if (!Schema::hasColumn('sites', 'button_selected_color')) $table->string('button_selected_color', 20)->nullable();
            if (!Schema::hasColumn('sites', 'button_selected_border_color')) $table->string('button_selected_border_color', 20)->nullable();
            if (!Schema::hasColumn('sites', 'menu_hover_color')) $table->string('menu_hover_color', 20)->nullable();
            if (!Schema::hasColumn('sites', 'menu_button_color')) $table->string('menu_button_color', 20)->nullable();
            if (!Schema::hasColumn('sites', 'action_button_color')) $table->string('action_button_color', 20)->nullable();
        });
    }

    public function down(): void
    {
    }
};
