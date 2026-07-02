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
        Schema::table('apostas', function (Blueprint $table) {
            if (!Schema::hasColumn('apostas', 'vendedor')) {
                $table->string('vendedor')->nullable()->after('total_palpites');
            }
            if (!Schema::hasColumn('apostas', 'cliente')) {
                $table->string('cliente')->nullable()->after('vendedor');
            }
            if (!Schema::hasColumn('apostas', 'cotacao')) {
                $table->decimal('cotacao', 15, 2)->default(0)->after('cliente');
            }
            if (!Schema::hasColumn('apostas', 'andamento_palpites')) {
                $table->integer('andamento_palpites')->default(0)->after('cotacao');
            }
            if (!Schema::hasColumn('apostas', 'acertos_palpites')) {
                $table->integer('acertos_palpites')->default(0)->after('andamento_palpites');
            }
            if (!Schema::hasColumn('apostas', 'erros_palpites')) {
                $table->integer('erros_palpites')->default(0)->after('acertos_palpites');
            }
            if (!Schema::hasColumn('apostas', 'devolvidos_palpites')) {
                $table->integer('devolvidos_palpites')->default(0)->after('erros_palpites');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('apostas', function (Blueprint $table) {
            $table->dropColumn([
                'vendedor', 'cliente', 'cotacao', 
                'andamento_palpites', 'acertos_palpites', 
                'erros_palpites', 'devolvidos_palpites'
            ]);
        });
    }
};
