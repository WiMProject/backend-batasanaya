<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Assets table
        Schema::create('assets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('file_name')->unique();
            $table->string('type'); // image, audio
            $table->string('category')->nullable();
            $table->string('subcategory')->nullable();
            $table->string('file');
            $table->integer('size');
            $table->foreignUuid('created_by_id')->nullable()
                  ->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        // Songs table
        Schema::create('songs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->string('file');
            $table->uuid('created_by_id');
            $table->foreign('created_by_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });

        // Videos table
        Schema::create('videos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->string('file');
            $table->json('qualities')->nullable();
            $table->uuid('created_by_id');
            $table->foreign('created_by_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });

        // Backgrounds table
        Schema::create('backgrounds', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('file');
            $table->integer('size');
            $table->boolean('is_active')->default(true);
            $table->foreignUuid('created_by_id')->nullable()
                  ->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('backgrounds');
        Schema::dropIfExists('videos');
        Schema::dropIfExists('songs');
        Schema::dropIfExists('assets');
    }
};
