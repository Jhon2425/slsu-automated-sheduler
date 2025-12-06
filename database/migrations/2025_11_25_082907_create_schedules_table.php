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
        $table->foreignId('program_id')->constrained('programs')->onDelete('cascade');
        $table->foreignId('faculty_enrollment_id')->nullable()->constrained('faculty_enrollments')->onDelete('cascade');
        $table->tinyInteger('day')->nullable()->comment('Day index (0-6) for ordering');
        $table->timestamps();
    });

    }

    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
