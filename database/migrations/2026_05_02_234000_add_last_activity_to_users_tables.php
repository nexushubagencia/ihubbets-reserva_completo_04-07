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
            if (!Schema::hasColumn('master_users', 'last_activity')) {
                $table->timestamp('last_activity')->nullable()->after('updated_at');
            }
        });

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'last_activity')) {
                    $table->timestamp('last_activity')->nullable()->after('updated_at');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_users', function (Blueprint $table) {
            $table->dropColumn('last_activity');
        });

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('last_activity');
            });
        }
    }
};
