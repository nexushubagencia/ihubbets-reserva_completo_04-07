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
        Schema::table('master_users', function (Blueprint $table) {
            if (!Schema::hasColumn('master_users', 'contato')) {
                $table->string('contato')->nullable()->after('email');
            }
            if (!Schema::hasColumn('master_users', 'address')) {
                $table->string('address')->nullable()->after('contato');
            }
            if (!Schema::hasColumn('master_users', 'prize_paid_percent')) {
                $table->decimal('prize_paid_percent', 5, 2)->default(0)->after('manager_commission_rate');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_users', function (Blueprint $table) {
            $table->dropColumn(['contato', 'address', 'prize_paid_percent']);
        });
    }
};
