<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOtpsTable extends Migration
{
    public function up()
    {
        Schema::create('otps', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('phone_number')->index();
            $table->string('code');
            $table->boolean('is_used')->default(false);
            $table->boolean('is_revoked')->default(false);
            $table->timestamp('expired_at');
            $table->timestamps(); // created_at dan updated_at

            // Foreign key ke tabel users berdasarkan phone_number
            $table->foreign('phone_number')->references('phone_number')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('otps');
    }
}