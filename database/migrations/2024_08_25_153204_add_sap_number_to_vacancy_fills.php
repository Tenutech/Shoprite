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
        Schema::table('vacancy_fills', function (Blueprint $table) {
            $table->string('sap_number', 8)->nullable()->after('applicant_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vacancy_fills', function (Blueprint $table) {
            $table->dropColumn('sap_number');
        });
    }
};
