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
        Schema::table('notifications', function (Blueprint $table) {
            // Add the applicant_id column after causer_id, with foreign key constraints
            $table->bigInteger('applicant_id')->unsigned()->nullable()->after('causer_id');

            // Add the foreign key constraint for applicant_id
            $table->foreign('applicant_id')
                  ->references('id')
                  ->on('applicants')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('notifications', function (Blueprint $table) {
            // Drop the foreign key constraint and the applicant_id column
            $table->dropForeign(['applicant_id']);
            $table->dropColumn('applicant_id');
        });
    }
};
