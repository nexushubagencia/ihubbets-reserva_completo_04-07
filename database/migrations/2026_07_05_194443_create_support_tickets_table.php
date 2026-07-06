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
        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('site_id')->default(1);
            $table->unsignedBigInteger('user_id');
            $table->string('subject', 255);
            $table->text('message');
            $table->enum('status', ['open', 'pending', 'closed'])->default('open');
            $table->text('admin_response')->nullable();
            $table->timestamps();

            $table->index(['site_id', 'user_id']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('support_tickets');
    }
};
