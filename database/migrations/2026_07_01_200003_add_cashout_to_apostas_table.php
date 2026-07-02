<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('apostas', function (Blueprint $table) {
            $table->decimal('cash_out_amount', 15, 2)->default(0)->after('comicao');
            $table->string('resultado_loto')->nullable()->after('devolvidos_palpites');
        });
    }

    public function down(): void
    {
        Schema::table('apostas', function (Blueprint $table) {
            $table->dropColumn(['cash_out_amount', 'resultado_loto']);
        });
    }
};
