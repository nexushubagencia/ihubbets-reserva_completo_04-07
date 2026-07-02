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
            if (!Schema::hasColumn('master_users', 'commission_rate')) {
                $table->decimal('commission_rate', 8, 2)->default(0)->after('balance');
            }
            if (!Schema::hasColumn('master_users', 'comissao_gerente')) {
                $table->decimal('comissao_gerente', 8, 2)->default(0)->after('balance');
            }
            if (!Schema::hasColumn('master_users', 'comissao_cambistas')) {
                $table->decimal('comissao_cambistas', 8, 2)->default(0)->after('comissao_gerente');
            }
            if (!Schema::hasColumn('master_users', 'can_create_coupons')) {
                $table->boolean('can_create_coupons')->default(0)->after('status');
            }
            if (!Schema::hasColumn('master_users', 'endereco')) {
                $table->string('endereco')->nullable()->after('address');
            }
            if (!Schema::hasColumn('master_users', 'situacao')) {
                $table->string('situacao')->default('ativo')->after('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_users', function (Blueprint $table) {
            $table->dropColumn(['commission_rate', 'comissao_gerente', 'comissao_cambistas']);
        });
    }
};
