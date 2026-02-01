<?php

// =============================================================================
// MIGRATION FILE: database/migrations/xxxx_xx_xx_create_subjects_table.php
// =============================================================================

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('course_code');
            $table->string('subject_name');
            $table->integer('lec')->default(0); // Lecture units
            $table->integer('lab')->default(0); // Laboratory units
            $table->integer('units')->default(0); // Total units
            $table->string('pre_req')->nullable(); // Pre-requisite
            $table->integer('year_level'); // 1, 2, 3, or 4
            $table->string('semester'); // First Semester, Second Semester
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('subjects');
    }
};
