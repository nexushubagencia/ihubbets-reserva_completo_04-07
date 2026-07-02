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
        Schema::table('manual_events', function (Blueprint $table) {
            $table->string('league_name')->nullable()->after('away_team');
            $table->string('home_flag')->nullable()->after('league_name');
            $table->string('away_flag')->nullable()->after('home_flag');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('manual_events', function (Blueprint $table) {
            //
        });
    }
};
