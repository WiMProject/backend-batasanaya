<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Roles table
        Schema::create('roles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->unique();
            $table->timestamps();
        });

        // Users table
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('full_name');
            $table->string('phone_number')->unique()->nullable();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('pin_code')->nullable();
            $table->string('profile_picture')->nullable();
            $table->foreignUuid('role_id')->constrained('roles');
            $table->timestamps();
        });

        // OTPs table
        Schema::create('otps', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('phone_number')->index();
            $table->string('code');
            $table->boolean('is_used')->default(false);
            $table->boolean('is_revoked')->default(false);
            $table->timestamp('expired_at');
            $table->timestamps();
            
            $table->foreign('phone_number')->references('phone_number')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('otps');
        Schema::dropIfExists('users');
        Schema::dropIfExists('roles');
    }
};
