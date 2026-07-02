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
        Schema::table('configuracaos', function (Blueprint $table) {
            $table->string('op_volei')->default('Sim')->after('op_tenis');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('configuracaos', function (Blueprint $table) {
            $table->dropColumn('op_volei');
        });
    }
};
