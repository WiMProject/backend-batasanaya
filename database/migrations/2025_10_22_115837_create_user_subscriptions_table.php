<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserSubscriptionsTable extends Migration
{
    public function up()
    {
        Schema::create('user_subscriptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('email');
            
            // Foreign key ke tabel users.
            // onDelete('set null') berarti jika user dihapus, kolom ini akan menjadi NULL.
            $table->foreignUuid('user_id')->nullable()->unique()
                  ->constrained('users')->onDelete('set null');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_subscriptions');
    }
}