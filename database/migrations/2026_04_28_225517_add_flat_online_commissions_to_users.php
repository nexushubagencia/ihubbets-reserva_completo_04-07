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
            $table->decimal('comissao_online', 5, 2)->default(0.00)->after('online_comissao10');
            $table->decimal('comissao_gerente_online', 5, 2)->default(0.00)->after('comissao_online');
        });
    }

    public function down(): void
    {
        Schema::table('master_users', function (Blueprint $table) {
            $table->dropColumn(['comissao_online', 'comissao_gerente_online']);
        });
    }
};
