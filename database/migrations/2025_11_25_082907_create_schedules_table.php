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

            // Foreign keys
            $table->foreignId('program_id')->constrained('programs')->onDelete('cascade');
            $table->foreignId('faculty_enrollment_id')->nullable()->constrained('faculty_enrollments')->onDelete('cascade');
            $table->foreignId('faculty_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->onDelete('set null');

            // Schedule details
            $table->string('year_level')->nullable(); // replaced section
            $table->string('class_type')->nullable(); // Lecture/Lab
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->tinyInteger('day')->nullable()->comment('Day index: 0=Sunday, 1=Monday,... 6=Saturday');
            $table->string('semester')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
