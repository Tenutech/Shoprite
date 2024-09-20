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
            // Modify the additional_contact_number column to be an ENUM with Yes and No
            $table->enum('additional_contact_number', ['Yes', 'No'])->nullable()->default(null)->change();
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
