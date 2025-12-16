<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('videos', function (Blueprint $table) {
            $table->dropColumn(['video_url', 'thumbnail_url']);
            $table->string('file')->after('title');
            $table->json('qualities')->nullable()->after('file');
        });
    }

    public function down()
    {
        Schema::table('videos', function (Blueprint $table) {
            $table->dropColumn(['file', 'qualities']);
            $table->text('video_url')->after('title');
            $table->text('thumbnail_url')->after('video_url');
        });
    }
};
