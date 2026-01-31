<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('classrooms', function (Blueprint $table) {
            $table->id();
            $table->string('room_name')->unique();
            $table->integer('capacity');
            $table->enum('type', ['Laboratory', 'Lecture', 'Both'])->default('Both');
            $table->timestamps();
        });

    }

    public function down()
    {
        Schema::dropIfExists('classrooms');
    }
};