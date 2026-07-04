<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('sites', function (Blueprint $table) {
            if (!Schema::hasColumn('sites', 'marketing_image_1')) {
                $table->string('marketing_image_1')->nullable();
            }
            if (!Schema::hasColumn('sites', 'marketing_image_2')) {
                $table->string('marketing_image_2')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->dropColumn(['marketing_image_1', 'marketing_image_2']);
        });
    }
};
