<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserPreferencesTable extends Migration
{
    public function up()
    {
        Schema::create('user_preferences', function (Blueprint $table) {
            $table->foreignUuid('id')->primary()
                  ->constrained('users')->onDelete('cascade');
            
            $table->boolean('audio_enabled')->default(true);
            $table->boolean('music_enabled')->default(true);
            $table->integer('max_screen_time')->default(7200); // 7200 detik = 2 jam

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_preferences');
    }
}