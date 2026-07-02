<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('block_odd_matches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('odd_id')->nullable();
            $table->string('odd_uid')->nullable();
            $table->string('odd')->nullable();
            $table->decimal('cotacao', 10, 2)->nullable();
            $table->boolean('status')->default(1);
            $table->unsignedBigInteger('site_id')->default(1);
            $table->timestamps();

            $table->index('odd_id');
            $table->index('site_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('block_odd_matches');
    }
};
