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

        // Seed sample classrooms
        DB::table('classrooms')->insert([
            ['room_name' => 'Classroom 101', 'capacity' => 40, 'type' => 'Lecture', 'created_at' => now(), 'updated_at' => now()],
            ['room_name' => 'Classroom 102', 'capacity' => 35, 'type' => 'Lecture', 'created_at' => now(), 'updated_at' => now()],
            ['room_name' => 'Computer Lab 201', 'capacity' => 30, 'type' => 'Laboratory', 'created_at' => now(), 'updated_at' => now()],
            ['room_name' => 'Computer Lab 202', 'capacity' => 25, 'type' => 'Laboratory', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('classrooms');
    }
};