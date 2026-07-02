<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('playfiver_providers', function (Blueprint $table) {
            $table->id();
            $table->integer('provider_id')->unique();
            $table->string('name');
            $table->string('image_url')->nullable();
            $table->string('wallet_name');
            $table->boolean('status')->default(true);
            $table->foreignId('site_id')->constrained('sites')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('playfiver_providers');
    }
};
