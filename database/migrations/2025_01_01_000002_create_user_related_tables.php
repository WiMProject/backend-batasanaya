<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // User Preferences
        Schema::create('user_preferences', function (Blueprint $table) {
            $table->foreignUuid('id')->primary()
                  ->constrained('users')->onDelete('cascade');
            $table->boolean('audio_enabled')->default(true);
            $table->boolean('music_enabled')->default(true);
            $table->integer('max_screen_time')->default(7200); // 2 hours
            $table->timestamps();
        });

        // User Subscriptions
        Schema::create('user_subscriptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('email');
            $table->foreignUuid('user_id')->nullable()->unique()
                  ->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_subscriptions');
        Schema::dropIfExists('user_preferences');
    }
};
