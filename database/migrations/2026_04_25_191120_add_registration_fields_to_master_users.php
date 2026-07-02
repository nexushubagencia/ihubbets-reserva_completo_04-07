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
            if (!Schema::hasColumn('master_users', 'cpf')) {
                $table->string('cpf', 14)->nullable()->unique()->after('contato');
            }
            if (!Schema::hasColumn('master_users', 'birth_date')) {
                $table->date('birth_date')->nullable()->after('cpf');
            }
            if (!Schema::hasColumn('master_users', 'pix_key_type')) {
                $table->string('pix_key_type', 50)->nullable()->after('pix_key');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_users', function (Blueprint $table) {
            $table->dropColumn(['cpf', 'birth_date', 'pix_key_type']);
        });
    }
};
