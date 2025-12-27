<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('full_name');
            $table->string('phone_number')->unique()->nullable();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('pin_code')->nullable();
            $table->string('profile_picture')->nullable();
            
            // Foreign key untuk relasi ke tabel roles
            $table->foreignUuid('role_id')->constrained('roles');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
}