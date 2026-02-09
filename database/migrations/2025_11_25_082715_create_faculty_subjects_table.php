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
        Schema::create('faculty_subjects', function (Blueprint $table) {
            $table->id();

            // Foreign keys
            $table->foreignId('faculty_id')
                ->constrained('users')
                ->onDelete('cascade');
            $table->foreignId('subject_id')
                ->constrained('subjects')
                ->onDelete('cascade');
            $table->foreignId('program_id')
                ->constrained('programs')
                ->onDelete('cascade');

            // Lecture and lab units
            $table->decimal('lecture_units', 4, 2)->unsigned()->default(0);
            $table->decimal('laboratory_units', 4, 2)->unsigned()->default(0);

            // Year level and semester
            $table->unsignedTinyInteger('year_level');
            $table->unsignedTinyInteger('semester');

            // Availability fields
            $table->string('availability')->nullable(); // e.g., "MWF", "TTh"
            $table->time('start_time')->nullable();    // e.g., 08:00:00
            $table->time('end_time')->nullable();      // e.g., 10:00:00
            $table->date('date')->nullable();          // specific date if needed

            // No timestamps based on your model
            // Remove this if you want timestamps: $table->timestamps();

            // Ensure a faculty can only be assigned to a subject once per program
            $table->unique(['faculty_id', 'subject_id', 'program_id']);

            // Indexes for better query performance
            $table->index('faculty_id');
            $table->index('subject_id');
            $table->index('program_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('faculty_subjects');
    }
};