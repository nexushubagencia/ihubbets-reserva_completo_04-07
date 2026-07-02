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
            for ($i = 1; $i <= 10; $i++) {
                $table->decimal("online_comissao{$i}", 8, 2)->default(0)->after("comissao10");
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_users', function (Blueprint $table) {
            for ($i = 1; $i <= 10; $i++) {
                $table->dropColumn("online_comissao{$i}");
            }
        });
    }
};
