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
        Schema::table('sites', function (Blueprint $table) {
            $table->string('social_facebook')->nullable()->after('social_instagram');
            $table->string('social_twitter')->nullable()->after('social_facebook');
            $table->string('social_youtube')->nullable()->after('social_twitter');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->dropColumn(['social_facebook', 'social_twitter', 'social_youtube']);
        });
    }
};
