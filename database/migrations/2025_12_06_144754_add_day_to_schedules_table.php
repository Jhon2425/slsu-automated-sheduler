<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // If the schedules table doesn't yet have these columns, add them.
        // We check using Schema::hasColumn to avoid errors when re-running migrations.
        if (!Schema::hasColumn('schedules', 'day') || !Schema::hasColumn('schedules', 'start_time')) {
            Schema::table('schedules', function (Blueprint $table) {
                // 'day' as tinyInteger: 0 = Sunday (or whatever mapping you use) up to 6
                // change to ->string('day')->nullable() if you store day as text.
                if (!Schema::hasColumn('schedules', 'day')) {
                    $table->tinyInteger('day')->nullable()->after('id')
                          ->comment('Day index (e.g. 0-6) for ordering');
                }

                // start_time and end_time as TIME columns so orderBy('start_time') works
                if (!Schema::hasColumn('schedules', 'start_time')) {
                    $table->time('start_time')->nullable()->after('day');
                }

                if (!Schema::hasColumn('schedules', 'end_time')) {
                    $table->time('end_time')->nullable()->after('start_time');
                }
            });
        }
    }

    public function down(): void
    {
        // Drop the columns if they exist
        Schema::table('schedules', function (Blueprint $table) {
            if (Schema::hasColumn('schedules', 'end_time')) {
                $table->dropColumn('end_time');
            }
            if (Schema::hasColumn('schedules', 'start_time')) {
                $table->dropColumn('start_time');
            }
            if (Schema::hasColumn('schedules', 'day')) {
                $table->dropColumn('day');
            }
        });
    }
};
