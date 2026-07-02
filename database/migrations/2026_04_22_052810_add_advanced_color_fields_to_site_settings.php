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
            $table->string('button_selected_color')->default('#ffc107')->nullable();
            $table->string('button_selected_border_color')->default('#ffc107')->nullable();
            $table->string('hover_menu_color')->default('#343a40')->nullable();
            $table->string('main_menu_button_color')->default('#000000')->nullable();
            $table->string('save_button_color')->default('#28a745')->nullable();
            $table->boolean('advanced_share')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn([
                'button_selected_color', 'button_selected_border_color',
                'hover_menu_color', 'main_menu_button_color', 'save_button_color',
                'advanced_share'
            ]);
        });
    }
};
