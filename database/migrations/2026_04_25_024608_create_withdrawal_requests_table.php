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
        Schema::create('withdrawal_requests', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->unsignedBigInteger('site_id');
            $blueprint->unsignedBigInteger('user_id');
            $blueprint->decimal('amount', 15, 2);
            $blueprint->string('pix_key');
            $blueprint->string('pix_key_type');
            $blueprint->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $blueprint->string('receipt_path')->nullable();
            $blueprint->text('admin_note')->nullable();
            $blueprint->timestamps();

            // Relacionamentos
            $blueprint->foreign('site_id')->references('id')->on('sites')->onDelete('cascade');
            $blueprint->foreign('user_id')->references('id')->on('master_users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('withdrawal_requests');
    }
};
