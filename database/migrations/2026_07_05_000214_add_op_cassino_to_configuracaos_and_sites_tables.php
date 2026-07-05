<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('configuracaos', 'op_cassino')) {
            Schema::table('configuracaos', function ($table) {
                $table->string('op_cassino')->default('Sim')->after('op_e_sports');
            });
        }

        if (!Schema::hasColumn('sites', 'op_cassino')) {
            Schema::table('sites', function ($table) {
                $table->string('op_cassino', 5)->default('Sim')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::table('configuracaos', function ($table) {
            $table->dropColumn('op_cassino');
        });
        Schema::table('sites', function ($table) {
            $table->dropColumn('op_cassino');
        });
    }
};
