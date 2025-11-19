<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBackgroundsTable extends Migration
{
    public function up()
    {
        Schema::create('backgrounds', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('file');
            $table->integer('size');
            $table->foreignUuid('created_by_id')->nullable()
                  ->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('backgrounds');
    }
}