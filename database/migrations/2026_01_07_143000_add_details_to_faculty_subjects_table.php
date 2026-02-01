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
            if (!Schema::hasColumn('faculty_subjects', 'program_id')) {
                $table->foreignId('program_id')
                      ->nullable()
                      ->after('subject_id')
                      ->constrained('programs')
                      ->nullOnDelete();
            }

            // Availability fields
            if (!Schema::hasColumn('faculty_subjects', 'availability')) {
                $table->string('availability')->nullable()->after('laboratory_units');
            }

            if (!Schema::hasColumn('faculty_subjects', 'start_time')) {
                $table->time('start_time')->nullable()->after('availability');
            }

            if (!Schema::hasColumn('faculty_subjects', 'end_time')) {
                $table->time('end_time')->nullable()->after('start_time');
            }

            if (!Schema::hasColumn('faculty_subjects', 'date')) {
                $table->date('date')->nullable()->after('end_time');
            }
        });
    }

    public function down(): void
    {
        Schema::table('faculty_subjects', function (Blueprint $table) {
            if (Schema::hasColumn('faculty_subjects', 'program_id')) {
                $table->dropForeign(['program_id']);
                $table->dropColumn('program_id');
            }

            $table->dropColumn([
                'availability',
                'start_time',
                'end_time',
                'date'
            ]);
        });
    }
};
