<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create educational_backgrounds table
        Schema::create('educational_backgrounds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('faculty_id')
                ->constrained('faculty')
                ->cascadeOnDelete();
            
            $table->string('faculty_code')->nullable();
            $table->foreign('faculty_code')
                ->references('faculty_code')
                ->on('faculty')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->enum('degree_earned', [
                'Bachelor Degree',
                'Master Degree',
                'Doctorate Degree',
                'Professional Degree',
            ]);

            $table->year('year_graduated');
            $table->string('course');
            $table->string('school_graduated');
            $table->timestamps();
        });

        // 2. Migrate existing data (only if old columns exist)
        if (
            Schema::hasColumn('faculty', 'degree_earned') &&
            Schema::hasColumn('faculty', 'year_graduated') &&
            Schema::hasColumn('faculty', 'course') &&
            Schema::hasColumn('faculty', 'school_graduated')
        ) {
            DB::table('faculty')->orderBy('id')->chunk(100, function ($faculties) {
                foreach ($faculties as $faculty) {
                    if (
                        $faculty->degree_earned &&
                        $faculty->year_graduated &&
                        $faculty->course &&
                        $faculty->school_graduated
                    ) {
                        DB::table('educational_backgrounds')->insert([
                            'faculty_id'        => $faculty->id,
                            'faculty_code'      => $faculty->faculty_code,
                            'degree_earned'     => $faculty->degree_earned,
                            'year_graduated'    => $faculty->year_graduated,
                            'course'            => $faculty->course,
                            'school_graduated'  => $faculty->school_graduated,
                            'created_at'        => $faculty->created_at,
                            'updated_at'        => $faculty->updated_at,
                        ]);
                    }
                }
            });
        }

        // 3. Safely remove old columns from faculty
        Schema::table('faculty', function (Blueprint $table) {
            $columns = [
                'degree_earned',
                'year_graduated',
                'course',
                'school_graduated',
            ];

            $existing = array_filter($columns, fn ($column) =>
                Schema::hasColumn('faculty', $column)
            );

            if (! empty($existing)) {
                $table->dropColumn($existing);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Restore columns to faculty (only if missing)
        Schema::table('faculty', function (Blueprint $table) {
            if (! Schema::hasColumn('faculty', 'degree_earned')) {
                $table->enum('degree_earned', [
                    'Bachelor Degree',
                    'Master Degree',
                    'Doctorate Degree',
                    'Professional Degree',
                ])->nullable();
            }

            if (! Schema::hasColumn('faculty', 'year_graduated')) {
                $table->year('year_graduated')->nullable();
            }

            if (! Schema::hasColumn('faculty', 'course')) {
                $table->string('course')->nullable();
            }

            if (! Schema::hasColumn('faculty', 'school_graduated')) {
                $table->string('school_graduated')->nullable();
            }
        });

        // 2. Move data back (first education per faculty)
        if (Schema::hasTable('educational_backgrounds')) {
            DB::table('educational_backgrounds')
                ->orderBy('faculty_id')
                ->orderBy('id')
                ->chunk(100, function ($educations) {
                    foreach ($educations as $education) {
                        DB::table('faculty')
                            ->where('id', $education->faculty_id)
                            ->whereNull('degree_earned')
                            ->update([
                                'degree_earned'    => $education->degree_earned,
                                'year_graduated'   => $education->year_graduated,
                                'course'           => $education->course,
                                'school_graduated' => $education->school_graduated,
                            ]);
                    }
                });
        }

        // 3. Drop table last
        Schema::dropIfExists('educational_backgrounds');
    }
};