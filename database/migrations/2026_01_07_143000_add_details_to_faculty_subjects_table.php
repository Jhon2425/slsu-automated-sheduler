<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('faculty_subjects', function (Blueprint $table) {

            // Program (optional but recommended)
            $table->foreignId('program_id')
                  ->nullable()
                  ->after('subject_id')
                  ->constrained('programs')
                  ->nullOnDelete();

            // Teaching load
            $table->decimal('lecture_units', 4, 1)
                  ->default(0)
                  ->after('program_id');

            $table->decimal('laboratory_units', 4, 1)
                  ->default(0)
                  ->after('lecture_units');
        });
    }

    public function down(): void
    {
        Schema::table('faculty_subjects', function (Blueprint $table) {
            $table->dropForeign(['program_id']);
            $table->dropColumn([
                'program_id',
                'lecture_units',
                'laboratory_units'
            ]);
        });
    }
};
