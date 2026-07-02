<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('master_users', function (Blueprint $table) {
            $table->decimal('comissao_bolao', 5, 2)->default(0)->after('comissao_loto');
        });
    }

    public function down(): void
    {
        Schema::table('master_users', function (Blueprint $table) {
            $table->dropColumn('comissao_bolao');
        });
    }
};
