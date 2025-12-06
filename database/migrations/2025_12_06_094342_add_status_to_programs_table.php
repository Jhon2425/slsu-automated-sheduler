<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up()
{
    Schema::table('programs', function (Blueprint $table) {
        $table->string('status')->default('active');
    });
}

    public function down(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            //
        });
    }
};
