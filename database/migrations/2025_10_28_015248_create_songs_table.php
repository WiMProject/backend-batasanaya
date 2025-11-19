<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSongsTable extends Migration
{
    public function up()
    {
        Schema::create('songs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title')->unique();
            $table->string('file'); // Path ke file lagu (misal: uploads/songs/lagu.mp3)
            $table->string('thumbnail'); // Path ke file thumbnail (misal: uploads/thumbnails/cover.jpg)

            $table->foreignUuid('created_by_id')->nullable()
                  ->constrained('users')->onDelete('set null');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('songs');
    }
}