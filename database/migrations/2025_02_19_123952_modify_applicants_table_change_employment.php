<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE `applicants` MODIFY `employment` ENUM('A','B','I','N','P','Y','R','S','F') NULL DEFAULT 'I'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE `applicants` MODIFY `employment` ENUM('A','B','I','N','P') NULL DEFAULT 'I'");
    }
};
