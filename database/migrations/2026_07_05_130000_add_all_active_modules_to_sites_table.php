<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $columns = [
            'active_affiliates',
            'active_payments',
            'active_mercado_pago',
            'active_loto',
            'active_marketing',
            'active_configuracoes',
            'active_riscos',
            'active_lancamentos',
            'active_extrato',
            'active_banner_generator',
            'active_gateway_deposito',
            'active_relatorios',
            'active_online_users',
        ];

        Schema::table('sites', function (Blueprint $table) use ($columns) {
            foreach ($columns as $col) {
                if (!Schema::hasColumn('sites', $col)) {
                    $table->boolean($col)->default(1)->after('active_casino');
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->dropColumn([
                'active_affiliates', 'active_payments', 'active_mercado_pago',
                'active_loto', 'active_marketing', 'active_configuracoes',
                'active_riscos', 'active_lancamentos', 'active_extrato',
                'active_banner_generator', 'active_gateway_deposito',
                'active_relatorios', 'active_online_users',
            ]);
        });
    }
};
