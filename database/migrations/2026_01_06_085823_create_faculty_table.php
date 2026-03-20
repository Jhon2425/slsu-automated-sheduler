<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Faculty table
        Schema::create('faculty', function (Blueprint $table) {
            $table->id();

            // Personal Information
            $table->string('faculty_code')->unique();
            $table->string('name');
            $table->enum('civil_status', ['Single', 'Married', 'Widowed', 'Divorced']);
            $table->date('birthdate');
            $table->enum('employment_status', ['Full-Time', 'Part-Time', 'Contractual']);
            $table->text('home_address');

            // Professional Information
            $table->float('years_of_service')->default(0);
            $table->string('rank')->nullable();
            $table->date('appointment_date')->nullable();

            // Program Assignment (required)
            $table->foreignId('program_id')->constrained('programs')->onDelete('restrict');

            // Account Credentials
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->timestamps();
            $table->softDeletes();
        });

        // Pivot table for faculty-subject assignments
        Schema::create('faculty_subject', function (Blueprint $table) {
            $table->id();
            $table->string('faculty_code');
            $table->foreignId('faculty_id')->constrained('faculty')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->foreignId('program_id')->nullable()->constrained('programs')->onDelete('set null');

            // Subject details
            $table->string('year_level')->nullable();
            $table->string('semester')->nullable();
            $table->decimal('lecture_units', 3, 1)->nullable();
            $table->decimal('laboratory_units', 3, 1)->nullable();
            $table->integer('ojt_hours')->nullable(); // OJT hours

            // Class information
            $table->integer('class_size')->default(0);

            $table->timestamps();

            $table->unique(['faculty_id', 'subject_id', 'program_id']);
            $table->index('faculty_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('faculty_subject');
        Schema::dropIfExists('faculty');
    }
};