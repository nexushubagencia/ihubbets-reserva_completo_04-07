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
            $table->boolean('is_manual')->default(false)->after('site_id');
            $table->unsignedBigInteger('manual_event_id')->nullable()->after('is_manual');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('featured_matches', function (Blueprint $table) {
            $table->dropColumn(['is_manual', 'manual_event_id']);
        });
    }
};
