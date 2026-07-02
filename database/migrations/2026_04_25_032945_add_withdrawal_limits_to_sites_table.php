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
        Schema::table('sites', function (Blueprint $table) {
            $table->decimal('min_withdrawal', 15, 2)->default(20.00)->after('pix_client_secret');
            $table->decimal('max_withdrawal', 15, 2)->default(1000.00)->after('min_withdrawal');
            $table->decimal('daily_withdrawal_limit', 15, 2)->default(5000.00)->after('max_withdrawal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            //
        });
    }
};
