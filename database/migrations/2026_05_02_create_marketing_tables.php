<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up()
    {
        if (!Schema::hasTable('banner_assets')) {
            Schema::create('banner_assets', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('type'); // background, player, logo, element, sticker
                $table->string('file_path');
                $table->timestamps();
            });
        } else {
            if (!Schema::hasColumn('banner_assets', 'type')) {
                Schema::table('banner_assets', function (Blueprint $table) {
                    $table->string('type')->after('name')->default('element');
                });
            }
        }

        if (!Schema::hasTable('banner_templates')) {
            Schema::create('banner_templates', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('type')->default('story'); // story, square, landscape
                $table->json('layout_data'); // Fabric.js JSON
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        // No down needed for this manual sync
    }
};
