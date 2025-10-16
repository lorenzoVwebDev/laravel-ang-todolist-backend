<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {   

        Schema::create('users', function (Blueprint $table) {
            $table->string('_id', length: 26)->primary()->unique();
            $table->string('username', 50)->unique();
            $table->string('email', 50)->unique();
            $table->string('password', 50);
            $table->binary('avatar')->nullable(true);
            $table->longText('refreshToken')->nullable(true);
            $table->json('roles');
            $table->bigInteger('datestamp');
            $table->integer('attempts')->nullable(true);
            $table->bigInteger('lastAttempt')->nullable(true);
            $table->bigInteger('validAttempt')->nullable(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users_models');
    }
};
