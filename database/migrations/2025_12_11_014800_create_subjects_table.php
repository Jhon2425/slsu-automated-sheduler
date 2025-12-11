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
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')
                ->constrained('programs')
                ->onDelete('cascade');
            $table->string('course_code', 20)->unique();
            $table->string('subject_name');
            $table->decimal('units', 3, 1);
            $table->enum('semester', ['1st Semester', '2nd Semester', 'Summer']);
            $table->tinyInteger('year_level');
            $table->timestamps();

            // Indexes for better query performance
            $table->index(['program_id', 'year_level', 'semester']);
            $table->index('course_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subjects');
    }
};