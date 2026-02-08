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
            $table->string('name');
            $table->enum('civil_status', ['Single', 'Married', 'Widowed', 'Divorced']);
            $table->date('birthdate');
            $table->enum('employment_status', ['Full-Time', 'Part-Time', 'Contractual']);
            $table->text('home_address');
            
            // Account Credentials (foreign key to users table)
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            $table->timestamps();
            $table->softDeletes();
        });

        // Pivot table for faculty-subject assignments
        Schema::create('faculty_subject', function (Blueprint $table) {
            $table->id();
            $table->foreignId('faculty_id')->constrained('faculty')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->foreignId('program_id')->nullable()->constrained()->onDelete('set null');
            
            // Subject details
            $table->string('year_level')->nullable(); // e.g., "1st Year", "2nd Year", "3rd Year", "4th Year"
            $table->string('semester')->nullable(); // e.g., "1st Semester", "2nd Semester", "Summer"
            $table->decimal('lecture_units', 3, 1)->nullable();
            $table->decimal('laboratory_units', 3, 1)->nullable();
            
            $table->timestamps();
            
            // Ensure unique combination of faculty, subject, and program
            $table->unique(['faculty_id', 'subject_id', 'program_id']);
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