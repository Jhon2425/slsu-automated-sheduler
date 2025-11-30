<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('faculties', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('course_subject');
            $table->integer('no_of_hours');
            $table->integer('units');
            $table->integer('no_of_students');
            $table->string('year_section');
            $table->enum('action_type', ['Examination', 'Laboratory', 'Lecture']);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('faculties');
    }
};