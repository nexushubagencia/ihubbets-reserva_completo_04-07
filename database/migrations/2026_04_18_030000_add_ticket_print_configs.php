<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            if (!Schema::hasColumn('sites', 'ticket_model')) $table->string('ticket_model', 20)->default('modelo_1');
            if (!Schema::hasColumn('sites', 'bluetooth_print_enabled')) $table->boolean('bluetooth_print_enabled')->default(true);
        });
    }

    public function down(): void
    {
    }
};
