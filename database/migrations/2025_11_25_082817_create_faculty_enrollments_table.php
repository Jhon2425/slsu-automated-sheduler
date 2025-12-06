<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('faculty_enrollments')) {
            Schema::create('faculty_enrollments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('faculty_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('program_id')->constrained('programs')->onDelete('cascade');
                $table->enum('enrollment_status', ['pending', 'active', 'completed'])->default('pending');
                
                // Faculty assignment details (filled by admin)
                $table->string('course_subject')->nullable();
                $table->string('year_section')->nullable();
                $table->integer('no_of_students')->nullable();
                $table->decimal('units', 5, 2)->nullable();
                $table->integer('no_of_hours')->nullable();
                $table->string('action_type')->nullable(); // Lecture, Laboratory, Both
                
                $table->timestamps();
                
                // Unique constraint to prevent duplicate enrollments
                $table->unique(['faculty_id', 'program_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('faculty_enrollments');
    }
};