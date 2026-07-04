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
        Schema::table('saques', function (Blueprint $table) {
            $table->timestamp('paid_at')->nullable()->after('status');
            $table->decimal('paid_amount', 15, 2)->nullable()->after('paid_at');
            $table->text('gateway_response')->nullable()->after('paid_amount');
        });

        Schema::table('pix_deposits', function (Blueprint $table) {
            $table->timestamp('paid_at')->nullable()->after('status');
            $table->decimal('paid_amount', 15, 2)->nullable()->after('paid_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('saques', function (Blueprint $table) {
            $table->dropColumn(['paid_at', 'paid_amount', 'gateway_response']);
        });

        Schema::table('pix_deposits', function (Blueprint $table) {
            $table->dropColumn(['paid_at', 'paid_amount']);
        });
    }
};
