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
        Schema::table('configuracaos', function (Blueprint $table) {
            $table->boolean('affiliate_enabled')->default(true)->after('site_id');
            $table->decimal('affiliate_commission', 5, 2)->default(10.00)->after('affiliate_enabled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('configuracaos', function (Blueprint $table) {
            $table->dropColumn(['affiliate_enabled', 'affiliate_commission']);
        });
    }
};
