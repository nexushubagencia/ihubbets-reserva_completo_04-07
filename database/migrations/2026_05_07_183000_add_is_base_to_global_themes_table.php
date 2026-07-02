<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsBaseToGlobalThemesTable extends Migration
{
    public function up()
    {
        Schema::table('global_themes', function (Blueprint $table) {
            if (!Schema::hasColumn('global_themes', 'is_base')) {
                $table->boolean('is_base')->default(false)->after('is_active');
            }
        });

        // Marcar os temas existentes como base
        \Illuminate\Support\Facades\DB::table('global_themes')
            ->whereIn('slug', ['verde-claro', 'roxo', 'azul-escuro'])
            ->update(['is_base' => true]);
    }

    public function down()
    {
        Schema::table('global_themes', function (Blueprint $table) {
            $table->dropColumn('is_base');
        });
    }
}
