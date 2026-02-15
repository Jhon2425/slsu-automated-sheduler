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
            $table->integer('years_of_service')->default(0);
            $table->string('rank')->nullable(); // e.g., Instructor, Assistant Professor, Associate Professor, Professor
            
            // Account Credentials (foreign key to users table)
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            $table->timestamps();
            $table->softDeletes();
        });

        // Pivot table for faculty-subject assignments
        Schema::create('faculty_subject', function (Blueprint $table) {
            $table->id();
            $table->string('faculty_code'); // Added faculty_code
            $table->foreignId('faculty_id')->constrained('faculty')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->foreignId('program_id')->nullable()->constrained()->onDelete('set null');
            
            // Subject details
            $table->string('year_level')->nullable();
            $table->string('semester')->nullable();
            $table->decimal('lecture_units', 3, 1)->nullable();
            $table->decimal('laboratory_units', 3, 1)->nullable();
            
            // Class information
            $table->integer('class_size')->default(0);
            
            $table->timestamps();
            
            // Ensure unique combination of faculty, subject, and program
            $table->unique(['faculty_id', 'subject_id', 'program_id']);
            
            // Add index for faculty_code for faster lookups
            $table->index('faculty_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('faculty_subject');
        Schema::dropIfExists('faculty');
    }
};