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
        Schema::create('applicants', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('phone')->nullable();
            $table->string('firstname')->nullable();
            $table->string('lastname')->nullable();
            $table->string('id_number')->nullable();
            $table->enum('id_verified', ['Yes', 'No'])->nullable();
            $table->text('location')->nullable();
            $table->unsignedBigInteger('town_id')->nullable()->index('applicants_town_id_foreign');
            $table->string('coordinates')->nullable();
            $table->string('contact_number')->nullable();
            $table->string('additional_contact_number')->nullable();
            $table->date('birth_date')->nullable();
            $table->integer('age')->nullable();
            $table->unsignedBigInteger('gender_id')->nullable()->index('applicants_gender_id_foreign');
            $table->unsignedBigInteger('race_id')->nullable()->index('applicants_race_id_foreign');
            $table->enum('has_email', ['Yes', 'No'])->nullable();
            $table->string('email')->nullable();
            $table->string('re_enter_email')->nullable();
            $table->enum('has_tax', ['Yes', 'No'])->nullable();
            $table->string('tax_number')->nullable();
            $table->enum('citizen', ['Yes', 'No'])->nullable();
            $table->integer('resident')->nullable();
            $table->enum('foreign_national', ['Yes', 'No'])->nullable();
            $table->enum('criminal', ['Yes', 'No'])->nullable();
            $table->string('avatar', 255)->nullable();
            $table->unsignedBigInteger('position_id')->nullable()->index('applicants_position_id_foreign');
            $table->text('position_specify')->nullable();
            $table->string('school')->nullable();
            $table->unsignedBigInteger('education_id')->nullable()->index('applicants_education_id_foreign');
            $table->enum('training', ['Yes', 'No'])->nullable();
            $table->string('other_training')->nullable();
            $table->enum('drivers_license', ['Yes', 'No'])->nullable();
            $table->string('drivers_license_code', 10)->nullable();
            $table->enum('job_previous', ['Yes', 'No'])->nullable();
            $table->unsignedBigInteger('reason_id')->nullable()->index('applicants_reason_id_foreign');
            $table->text('job_leave_specify')->nullable();
            $table->string('job_business')->nullable();
            $table->text('job_position')->nullable();
            $table->unsignedBigInteger('duration_id')->nullable()->index('applicants_duration_id_foreign');
            $table->string('job_salary')->nullable();
            $table->string('job_reference_name')->nullable();
            $table->string('job_reference_phone')->nullable();
            $table->unsignedBigInteger('retrenchment_id')->nullable()->index('applicants_retrenchment_id_foreign');
            $table->text('job_retrenched_specify')->nullable();
            $table->unsignedBigInteger('brand_id')->nullable()->index('applicants_brand_id_foreign');
            $table->unsignedBigInteger('previous_job_position_id')->nullable()->index('applicants_previous_job_position_id_foreign');
            $table->text('job_shoprite_position_specify')->nullable();
            $table->text('job_shoprite_leave')->nullable();
            $table->unsignedBigInteger('transport_id')->nullable()->index('applicants_transport_id_foreign');
            $table->text('transport_specify')->nullable();
            $table->unsignedBigInteger('disability_id')->nullable()->index('applicants_disability_id_foreign');
            $table->text('illness_specify')->nullable();
            $table->date('commencement')->nullable();
            $table->unsignedBigInteger('type_id')->nullable()->index('applicants_type_id_foreign');
            $table->text('application_reason_specify')->nullable();
            $table->enum('relocate', ['Yes', 'No'])->nullable();
            $table->text('relocate_town')->nullable();
            $table->enum('vacancy', ['Yes', 'No'])->nullable();
            $table->enum('shift', ['Yes', 'No'])->nullable();
            $table->enum('has_bank_account', ['Yes', 'No'])->nullable();
            $table->unsignedBigInteger('bank_id')->nullable()->index('applicants_bank_id_foreign');
            $table->string('bank_specify')->nullable();
            $table->string('bank_number', 20)->nullable();
            $table->text('expected_salary')->nullable();
            $table->string('literacy_question_pool')->nullable();
            $table->integer('literacy_score')->nullable();
            $table->integer('literacy_questions')->nullable();
            $table->string('literacy', 10)->nullable();
            $table->string('numeracy_question_pool')->nullable();
            $table->integer('numeracy_score')->nullable();
            $table->integer('numeracy_questions')->nullable();
            $table->string('numeracy', 10)->nullable();
            $table->float('score', 10, 0)->nullable();
            $table->unsignedBigInteger('role_id')->nullable()->index('applicants_role_id_foreign');
            $table->unsignedBigInteger('applicant_type_id')->nullable()->index('applicants_applicant_type_id_foreign');
            $table->unsignedBigInteger('shortlist_id')->nullable()->index('applicants_shortlist_id_foreign');
            $table->unsignedBigInteger('appointed_id')->nullable()->index('applicants_appointed_id_foreign');
            $table->unsignedBigInteger('state_id')->nullable()->index('applicants_state_id_foreign');
            $table->enum('checkpoint', ['Yes', 'No'])->nullable()->default('No');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('applicants');
    }
};
