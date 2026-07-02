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
            // Remove o índice único global (que causava o erro 500 ao repetir login em bancas diferentes)
            $table->dropUnique('master_users_username_unique');
            
            // Adiciona o índice único composto (site_id + username)
            // Isso permite que o login 'joao' exista na Banca A e na Banca B ao mesmo tempo
            $table->unique(['site_id', 'username']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_users', function (Blueprint $table) {
            $table->dropUnique(['site_id', 'username']);
            $table->unique('username');
        });
    }
};
