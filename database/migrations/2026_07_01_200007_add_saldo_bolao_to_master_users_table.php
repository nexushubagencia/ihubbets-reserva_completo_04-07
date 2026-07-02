<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('master_users', function (Blueprint $table) {
            $table->decimal('saldo_bolao', 15, 2)->default(0)->after('saldo_loto');
            $table->decimal('entrada_bolao', 15, 2)->default(0)->after('entrada_loto');
        });
    }

    public function down(): void
    {
        Schema::table('master_users', function (Blueprint $table) {
            $table->dropColumn(['saldo_bolao', 'entrada_bolao']);
        });
    }
};
