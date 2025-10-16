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
/*         Schema::create('tasks', function (Blueprint $table) {
            $table->integer('id')->primary()->nullable(false);
            $table->text('_user_id')->nullable(false);
            $table->string('name')->nullable(false);
            $table->text('description')->nullable(false);
            $table->bigInteger('dueDate')->nullable(true)->defautl(null);
            $table->bigInteger('addDate');
            $table->boolean('done');
        }); */
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks_models');
    }
};

/* INSERT INTO tasks VALUES(7,
    'abg58gd79',
    'Lorenzo',
    'gay',
    NULL,
    154843571,
    true
    ); */