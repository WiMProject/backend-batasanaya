<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Progress table - 15 levels per user
        Schema::create('pasangkanhuruf_progress', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->integer('level_number'); // 1-15
            $table->boolean('is_unlocked')->default(false);
            $table->boolean('is_completed')->default(false);
            $table->integer('best_score')->default(0);
            $table->integer('best_time')->default(0);
            $table->integer('stars')->default(0); // 0-3
            $table->integer('attempts')->default(0);
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['user_id', 'level_number']);
        });
        
        // Sessions table - history setiap gameplay
        Schema::create('pasangkanhuruf_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->integer('level_number');
            $table->integer('score');
            $table->integer('time_taken'); // seconds
            $table->integer('correct_matches');
            $table->integer('wrong_matches');
            $table->integer('stars'); // 1-3
            $table->timestamp('completed_at');
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('pasangkanhuruf_sessions');
        Schema::dropIfExists('pasangkanhuruf_progress');
    }
};
