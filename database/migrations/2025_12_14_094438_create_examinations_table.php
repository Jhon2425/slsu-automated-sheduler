<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('examinations', function (Blueprint $table) {
            $table->id();

            // Core Foreign Keys
            $table->foreignId('faculty_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->foreignId('classroom_id')->constrained('classrooms')->onDelete('cascade');

            // Exam Schedule Details
            $table->date('exam_date');
            $table->tinyInteger('day')->comment('Day number: 1=Monday, 2=Tuesday... 7=Sunday');
            $table->time('start_time');
            $table->time('end_time');

            // Exam Information
            $table->enum('exam_type', ['Midterm', 'Final', 'Quiz'])->default('Final');
            $table->string('year_level')->nullable();
            $table->string('semester')->nullable();
            $table->string('year_section')->nullable();

            // Status
            $table->boolean('is_active')->default(true);
            $table->string('academic_year')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['faculty_id', 'exam_date', 'start_time']);
            $table->index(['classroom_id', 'exam_date', 'start_time']);
            $table->index('exam_date');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('examinations');
    }
};