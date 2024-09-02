<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vacancy_fills', function (Blueprint $table) {
            // Add the sap_number_id column
            $table->unsignedBigInteger('sap_number_id')->nullable()->after('applicant_id');

            // Optionally, add a foreign key constraint
            $table->foreign('sap_number_id')
                  ->references('id')
                  ->on('sap_numbers')
                  ->onDelete('cascade')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vacancy_fills', function (Blueprint $table) {
            // Drop the foreign key constraint first if it exists
            $table->dropForeign(['sap_number_id']);

            // Drop the sap_number_id column
            $table->dropColumn('sap_number_id');
        });
    }
};
