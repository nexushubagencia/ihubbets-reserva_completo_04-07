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
            $table->boolean('is_featured')->default(false)->after('status');
            $table->string('img_featured')->nullable()->after('is_featured');
            $table->string('cor_badge')->nullable()->after('img_featured');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('manual_events', function (Blueprint $table) {
            $table->dropColumn(['is_featured', 'img_featured', 'cor_badge']);
        });
    }
};
