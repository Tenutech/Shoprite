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
            $table->foreign(['reason_id'])->references(['id'])->on('reasons')->onUpdate('CASCADE')->onDelete('SET NULL');
            $table->foreign(['disability_id'])->references(['id'])->on('disabilities')->onUpdate('CASCADE')->onDelete('SET NULL');
            $table->foreign(['role_id'])->references(['id'])->on('roles')->onUpdate('CASCADE')->onDelete('SET NULL');
            $table->foreign(['education_id'])->references(['id'])->on('educations')->onUpdate('CASCADE')->onDelete('SET NULL');
            $table->foreign(['state_id'])->references(['id'])->on('states')->onUpdate('CASCADE')->onDelete('SET NULL');
            $table->foreign(['position_id'])->references(['id'])->on('positions')->onUpdate('CASCADE')->onDelete('SET NULL');
            $table->foreign(['appointed_id'])->references(['id'])->on('vacancy_fills')->onUpdate('CASCADE')->onDelete('SET NULL');
            $table->foreign(['transport_id'])->references(['id'])->on('transports')->onUpdate('CASCADE')->onDelete('SET NULL');
            $table->foreign(['race_id'])->references(['id'])->on('races')->onUpdate('CASCADE')->onDelete('SET NULL');
            $table->foreign(['brand_id'])->references(['id'])->on('brands')->onUpdate('CASCADE')->onDelete('SET NULL');
            $table->foreign(['retrenchment_id'])->references(['id'])->on('retrenchments')->onUpdate('CASCADE')->onDelete('SET NULL');
            $table->foreign(['duration_id'])->references(['id'])->on('durations')->onUpdate('CASCADE')->onDelete('SET NULL');
            $table->foreign(['shortlist_id'])->references(['id'])->on('shortlists')->onUpdate('CASCADE')->onDelete('SET NULL');
            $table->foreign(['gender_id'])->references(['id'])->on('genders')->onUpdate('CASCADE')->onDelete('SET NULL');
            $table->foreign(['applicant_type_id'])->references(['id'])->on('applicant_types')->onUpdate('CASCADE')->onDelete('SET NULL');
            $table->foreign(['town_id'])->references(['id'])->on('towns')->onUpdate('CASCADE')->onDelete('SET NULL');
            $table->foreign(['previous_job_position_id'])->references(['id'])->on('positions')->onUpdate('CASCADE')->onDelete('SET NULL');
            $table->foreign(['bank_id'])->references(['id'])->on('banks')->onUpdate('CASCADE')->onDelete('SET NULL');
            $table->foreign(['type_id'])->references(['id'])->on('types')->onUpdate('CASCADE')->onDelete('SET NULL');
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
            $table->dropForeign('applicants_reason_id_foreign');
            $table->dropForeign('applicants_disability_id_foreign');
            $table->dropForeign('applicants_role_id_foreign');
            $table->dropForeign('applicants_education_id_foreign');
            $table->dropForeign('applicants_state_id_foreign');
            $table->dropForeign('applicants_position_id_foreign');
            $table->dropForeign('applicants_appointed_id_foreign');
            $table->dropForeign('applicants_transport_id_foreign');
            $table->dropForeign('applicants_race_id_foreign');
            $table->dropForeign('applicants_brand_id_foreign');
            $table->dropForeign('applicants_retrenchment_id_foreign');
            $table->dropForeign('applicants_duration_id_foreign');
            $table->dropForeign('applicants_shortlist_id_foreign');
            $table->dropForeign('applicants_gender_id_foreign');
            $table->dropForeign('applicants_applicant_type_id_foreign');
            $table->dropForeign('applicants_town_id_foreign');
            $table->dropForeign('applicants_previous_job_position_id_foreign');
            $table->dropForeign('applicants_bank_id_foreign');
            $table->dropForeign('applicants_type_id_foreign');
        });
    }
};
