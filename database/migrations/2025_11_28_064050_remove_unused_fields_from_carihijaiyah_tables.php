<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('carihijaiyah_progress', function (Blueprint $table) {
            $table->dropColumn(['best_score', 'best_time', 'stars']);
        });
        
        Schema::table('carihijaiyah_sessions', function (Blueprint $table) {
            $table->dropColumn(['score', 'time_taken', 'stars']);
        });
    }

    public function down(): void
    {
        Schema::table('carihijaiyah_progress', function (Blueprint $table) {
            $table->integer('best_score')->default(0);
            $table->integer('best_time')->nullable();
            $table->integer('stars')->default(0);
        });
        
        Schema::table('carihijaiyah_sessions', function (Blueprint $table) {
            $table->integer('score')->default(0);
            $table->integer('time_taken')->nullable();
            $table->integer('stars')->default(0);
        });
    }
};
