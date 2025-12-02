<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveUnusedFieldsFromPasangkanhurufTables extends Migration
{
    public function up()
    {
        Schema::table('pasangkanhuruf_progress', function (Blueprint $table) {
            $table->dropColumn(['best_score', 'best_time', 'stars']);
        });

        Schema::table('pasangkanhuruf_sessions', function (Blueprint $table) {
            $table->dropColumn(['score', 'time_taken', 'correct_matches', 'wrong_matches', 'stars']);
        });
    }

    public function down()
    {
        Schema::table('pasangkanhuruf_progress', function (Blueprint $table) {
            $table->integer('best_score')->default(0);
            $table->integer('best_time')->default(0);
            $table->integer('stars')->default(0);
        });

        Schema::table('pasangkanhuruf_sessions', function (Blueprint $table) {
            $table->integer('score')->default(0);
            $table->integer('time_taken')->default(0);
            $table->integer('correct_matches')->default(0);
            $table->integer('wrong_matches')->default(0);
            $table->integer('stars')->default(0);
        });
    }
}
