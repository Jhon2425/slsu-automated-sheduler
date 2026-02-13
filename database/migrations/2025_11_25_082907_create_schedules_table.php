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
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();

            // Core Foreign Keys - Direct references (SIMPLIFIED)
            $table->foreignId('faculty_id')->constrained('users')->onDelete('cascade');
            $table->string('faculty_code')->nullable()->comment('Denormalized faculty code for queries');
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->foreignId('classroom_id')->constrained('classrooms')->onDelete('cascade');
            $table->foreignId('program_id')->nullable()->constrained('programs')->onDelete('set null');

            // Optional: Link back to the faculty_subject assignment
            $table->foreignId('faculty_subject_id')->nullable()->constrained('faculty_subjects')->onDelete('set null');

            // Schedule Time Details
            $table->tinyInteger('day')->comment('Day number: 1=Monday, 2=Tuesday... 7=Sunday');
            $table->string('day_name')->nullable()->comment('Day name: Monday, Tuesday, etc.');
            $table->time('start_time');
            $table->time('end_time');
            $table->date('schedule_date')->nullable()->comment('Specific date for this schedule');

            // Class Information
            $table->enum('class_type', ['Lecture', 'Laboratory'])->default('Lecture');
            $table->string('year_level')->nullable()->comment('e.g., 1, 2, 3, 4');
            $table->string('year_section')->nullable()->comment('Combined year and section e.g., 1-A, 2-B');
            $table->string('semester')->nullable()->comment('e.g., 1st Semester, 2nd Semester');

            // Optional: Section/Group information
            $table->string('section')->nullable()->comment('e.g., A, B, C or combined like 1-A');

            // Optional: Academic Year
            $table->string('academic_year')->nullable()->comment('e.g., 2024-2025');

            // Status
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // Indexes for better query performance
            $table->index(['faculty_id', 'day', 'start_time', 'end_time'], 'schedules_faculty_day_time_index');
            $table->index(['faculty_code', 'day', 'start_time', 'end_time'], 'schedules_faculty_code_day_time_index');
            $table->index(['classroom_id', 'day', 'start_time', 'end_time'], 'schedules_classroom_day_time_index');
            $table->index(['subject_id', 'year_level'], 'schedules_subject_year_index');
            $table->index('is_active');
            $table->index('academic_year');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};