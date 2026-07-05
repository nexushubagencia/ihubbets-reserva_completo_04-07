<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('apostas_cassino');
        Schema::dropIfExists('playfiver_games');
        Schema::dropIfExists('playfiver_providers');
    }

    public function down(): void
    {
        // Tables removed permanently; recreate only if rollback needed later.
    }
};
