<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();

            // Core Foreign Keys - Direct references (SIMPLIFIED)
            $table->foreignId('faculty_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->foreignId('classroom_id')->constrained('classrooms')->onDelete('cascade');

            // Optional: Link back to the faculty_subject assignment
            $table->foreignId('faculty_subject_id')->nullable()->constrained('faculty_subjects')->onDelete('set null');

            // Schedule Time Details
            $table->tinyInteger('day')->comment('Day number: 1=Monday, 2=Tuesday... 7=Sunday');
            $table->time('start_time');
            $table->time('end_time');
            $table->date('schedule_date')->nullable()->comment('Specific date for this schedule');

            // Class Information
            $table->enum('class_type', ['Lecture', 'Laboratory'])->default('Lecture');
            $table->string('year_level')->nullable()->comment('e.g., 1, 2, 3, 4');
            $table->string('semester')->nullable()->comment('e.g., 1st Semester, 2nd Semester');

            // Optional: Section/Group information
            $table->string('section')->nullable()->comment('e.g., A, B, C or combined like 1-A');

            // Optional: Academic Year
            $table->string('academic_year')->nullable()->comment('e.g., 2024-2025');

            // Status
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // Indexes for better query performance
            $table->index(['faculty_id', 'day', 'start_time', 'end_time']);
            $table->index(['classroom_id', 'day', 'start_time', 'end_time']);
            $table->index(['subject_id', 'year_level']);
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};