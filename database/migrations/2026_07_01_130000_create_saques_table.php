<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('saques')) {
            Schema::create('saques', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('site_id')->default(1);
                $table->string('status')->default('Em processamento');
                $table->decimal('valor', 10, 2);
                $table->string('pix');
                $table->string('tipo_pix')->default('random');
                $table->string('admin_note')->nullable();
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('master_users')->onDelete('cascade');
                $table->index(['site_id', 'status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('saques');
    }
};
