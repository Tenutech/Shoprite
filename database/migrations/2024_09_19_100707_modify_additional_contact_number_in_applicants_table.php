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
        Schema::table('applicants', function (Blueprint $table) {
            // Check if the column exists before attempting to drop it
            if (Schema::hasColumn('applicants', 'additional_contact_number')) {
                // Drop the additional_contact_number column if it exists
                $table->dropColumn('additional_contact_number');
            }

            // Re-add the additional_contact_number column as an enum after terms_conditions
            $table->enum('additional_contact_number', ['Yes', 'No'])
                  ->nullable()
                  ->default(null)
                  ->after('terms_conditions');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('applicants', function (Blueprint $table) {
            // Revert the additional_contact_number column back to its original state
            $table->string('additional_contact_number')->nullable()->default(null)->change();
        });
    }
};
