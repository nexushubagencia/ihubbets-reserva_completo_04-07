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
        Schema::table('apifootball_leagues', function (Blueprint $table) {
            $table->integer('season')->nullable()->default(date('Y'))->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('apifootball_leagues', function (Blueprint $table) {
            $table->integer('season')->nullable(false)->change();
        });
    }
};
