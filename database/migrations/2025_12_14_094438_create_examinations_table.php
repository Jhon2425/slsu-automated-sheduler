<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('examinations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('faculty_id');
            $table->unsignedBigInteger('subject_id');
            $table->unsignedBigInteger('classroom_id');
            $table->date('exam_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('year_level')->nullable();
            $table->string('semester')->nullable();
            $table->timestamps();

            // Optional: foreign keys
            // $table->foreign('faculty_id')->references('id')->on('users')->onDelete('cascade');
            // $table->foreign('subject_id')->references('id')->on('subjects')->onDelete('cascade');
            // $table->foreign('classroom_id')->references('id')->on('classrooms')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('examinations');
    }
};
