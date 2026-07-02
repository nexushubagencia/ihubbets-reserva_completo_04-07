<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quina_taxas', function (Blueprint $table) {
            $table->id();
            $table->string('dezena');
            $table->decimal('taxa', 10, 2)->default(0);
            $table->boolean('status')->default(1);
            $table->unsignedBigInteger('site_id')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quina_taxas');
    }
};
