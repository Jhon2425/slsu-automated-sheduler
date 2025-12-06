<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('programs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Add program and department to users table
        Schema::table('users', function (Blueprint $table) {
            $table->string('program')->nullable(); // For admin - text field
            $table->string('faculty_id')->nullable(); // For faculty - text field
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['program', 'faculty_id']);
        });
        
        Schema::dropIfExists('programs');
    }
};