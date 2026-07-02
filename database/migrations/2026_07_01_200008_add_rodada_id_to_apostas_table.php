<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('apostas', function (Blueprint $table) {
            $table->unsignedBigInteger('rodada_id')->nullable()->after('resultado_loto');
        });
    }

    public function down(): void
    {
        Schema::table('apostas', function (Blueprint $table) {
            $table->dropColumn('rodada_id');
        });
    }
};
