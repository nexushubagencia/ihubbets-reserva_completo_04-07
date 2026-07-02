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
        Schema::table('featured_matches', function (Blueprint $table) {
            $table->string('background_path')->nullable()->after('league_name');
            $table->string('badge_color')->nullable()->default('#ae8a36')->after('background_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('featured_matches', function (Blueprint $table) {
            $table->dropColumn(['background_path', 'badge_color']);
        });
    }
};
