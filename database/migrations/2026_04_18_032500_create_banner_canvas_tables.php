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
        if (!Schema::hasTable('banner_templates')) {
            Schema::create('banner_templates', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->enum('type', ['story', 'square', 'landscape'])->default('story'); // 1080x1920, 1080x1080, etc.
                $table->json('layout_data'); // Posições, fontes, cores e placeholders ({{team_home}}, {{odds_1}}, etc.)
                $table->string('background_path')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('banner_assets')) {
            // Tabela para fundos e ícones globais
            Schema::create('banner_assets', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->enum('type', ['background', 'icon', 'player', 'logo'])->default('background');
                $table->string('file_path');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banner_templates');
        Schema::dropIfExists('banner_assets');
    }
};
