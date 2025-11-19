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
        // Letter pairs for matching game
        Schema::create('letter_pairs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('letter_name'); // e.g., 'alif', 'ba', 'ta'
            $table->string('outline_image'); // kerangka huruf
            $table->string('complete_image'); // huruf lengkap
            $table->integer('difficulty_level')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Game sessions
        Schema::create('game_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->integer('total_pairs');
            $table->integer('correct_matches')->default(0);
            $table->integer('wrong_matches')->default(0);
            $table->integer('score')->default(0);
            $table->integer('time_taken')->nullable(); // in seconds
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // Individual matches in a game
        Schema::create('game_matches', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('game_session_id');
            $table->uuid('letter_pair_id');
            $table->boolean('is_correct');
            $table->timestamp('attempt_time');
            $table->timestamps();
            
            $table->foreign('game_session_id')->references('id')->on('game_sessions')->onDelete('cascade');
            $table->foreign('letter_pair_id')->references('id')->on('letter_pairs')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_matches');
        Schema::dropIfExists('game_sessions');
        Schema::dropIfExists('letter_pairs');
    }
};
