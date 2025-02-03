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
        if (!Schema::hasColumn('vacancies', 'deleted')) {
            Schema::table('vacancies', function (Blueprint $table) {
                $table->enum('deleted', ['Yes', 'No'])->default('No')->after('advertisement');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('vacancies', 'deleted')) {
            Schema::table('vacancies', function (Blueprint $table) {
                $table->dropColumn('deleted');
            });
        }
    }
};
