<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('sites', 'active_casino')) {
            Schema::table('sites', function (Blueprint $table) {
                $table->boolean('active_casino')->default(0)->after('active_bonus');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('sites', 'active_casino')) {
            Schema::table('sites', function (Blueprint $table) {
                $table->dropColumn('active_casino');
            });
        }
    }
};
