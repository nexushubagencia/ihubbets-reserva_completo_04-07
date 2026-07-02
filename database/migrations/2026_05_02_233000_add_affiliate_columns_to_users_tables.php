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
            if (!Schema::hasColumn('master_users', 'affiliate_id')) {
                $table->unsignedBigInteger('affiliate_id')->nullable()->after('id');
            }
            if (!Schema::hasColumn('master_users', 'referred_by_id')) {
                $table->unsignedBigInteger('referred_by_id')->nullable()->after('affiliate_id');
            }
        });

        // Also add to 'users' table just to keep sync
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'affiliate_id')) {
                    $table->unsignedBigInteger('affiliate_id')->nullable()->after('id');
                }
                if (!Schema::hasColumn('users', 'referred_by_id')) {
                    $table->unsignedBigInteger('referred_by_id')->nullable()->after('affiliate_id');
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
            $table->dropColumn(['affiliate_id', 'referred_by_id']);
        });

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn(['affiliate_id', 'referred_by_id']);
            });
        }
    }
};
