<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssetsTable extends Migration
{
    public function up()
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('file_name')->unique();
            $table->string('type');
            $table->string('file'); // Ini akan menyimpan path ke file
            $table->integer('size'); // Ukuran file dalam bytes

            // Foreign key ke user yang meng-upload
            $table->foreignUuid('created_by_id')->nullable()
                  ->constrained('users')->onDelete('set null');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('assets');
    }
}