<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('faculty_unavailabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('faculty_id')
                  ->constrained('faculty')
                  ->cascadeOnDelete();
            $table->string('faculty_code'); // Added faculty_code

            $table->string('day');
            $table->time('time_from');
            $table->time('time_to');
            $table->string('reason')->nullable();

            $table->timestamps();
            
            // Optional: Add index for better query performance
            $table->index(['faculty_code', 'day']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('faculty_unavailabilities');
    }
};