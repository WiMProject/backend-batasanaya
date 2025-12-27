<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveMatchesFromCarihijaiyahSessions extends Migration
{
    public function up()
    {
        Schema::table('carihijaiyah_sessions', function (Blueprint $table) {
            $table->dropColumn(['correct_matches', 'wrong_matches']);
        });
    }

    public function down()
    {
        Schema::table('carihijaiyah_sessions', function (Blueprint $table) {
            $table->integer('correct_matches')->default(0);
            $table->integer('wrong_matches')->default(0);
        });
    }
}
