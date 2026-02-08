<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
     public function up()
    {
        Schema::table('faculty', function (Blueprint $table) {
            if (Schema::hasColumn('faculty', 'degree_earned')) {
                $table->dropColumn([
                    'degree_earned',
                    'year_graduated',
                    'course',
                    'school_graduated',
                ]);
            }
        });
    }

    public function down()
    {
        Schema::table('faculty', function (Blueprint $table) {
            $table->string('degree_earned')->nullable();
            $table->year('year_graduated')->nullable();
            $table->string('course')->nullable();
            $table->string('school_graduated')->nullable();
        });
    }

};
