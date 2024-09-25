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
            // Dropping foreign key constraints before removing columns
            $table->dropForeign(['brand_id']);
            $table->dropForeign(['position_id']);
            $table->dropForeign(['reason_id']);
            $table->dropForeign(['retrenchment_id']);
            $table->dropForeign(['previous_job_position_id']);
            $table->dropForeign(['transport_id']);
            $table->dropForeign(['disability_id']);
            $table->dropForeign(['type_id']);
            $table->dropForeign(['bank_id']);

            // Dropping specified columns
            $table->dropColumn([
                'brand_id',
                're_enter_email',
                'has_tax',
                'tax_number',
                'citizen',
                'resident',
                'foreign_national',
                'criminal',
                'position_id',
                'position_specify',
                'school',
                'training',
                'other_training',
                'drivers_license',
                'drivers_license_code',
                'job_previous',
                'reason_id',
                'job_leave_specify',
                'job_business',
                'job_position',
                'job_salary',
                'job_reference_name',
                'job_reference_phone',
                'retrenchment_id',
                'job_retrenched_specify',
                'previous_job_position_id',
                'job_shoprite_position_specify',
                'job_shoprite_leave',
                'transport_id',
                'transport_specify',
                'disability_id',
                'illness_specify',
                'commencement',
                'type_id',
                'application_reason_specify',
                'relocate',
                'relocate_town',
                'vacancy',
                'shift',
                'has_bank_account',
                'bank_id',
                'bank_specify',
                'bank_number',
                'expected_salary'
            ]);
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
            // Adding back the columns
            $table->string('re_enter_email')->nullable();
            $table->enum('has_tax', ['Yes', 'No'])->nullable();
            $table->string('tax_number')->nullable();
            $table->enum('citizen', ['Yes', 'No'])->nullable();
            $table->integer('resident')->nullable();
            $table->enum('foreign_national', ['Yes', 'No'])->nullable();
            $table->enum('criminal', ['Yes', 'No'])->nullable();
            $table->unsignedBigInteger('position_id')->nullable()->index();
            $table->text('position_specify')->nullable();
            $table->string('school')->nullable();
            $table->enum('training', ['Yes', 'No'])->nullable();
            $table->string('other_training')->nullable();
            $table->enum('drivers_license', ['Yes', 'No'])->nullable();
            $table->string('drivers_license_code', 10)->nullable();
            $table->enum('job_previous', ['Yes', 'No'])->nullable();
            $table->unsignedBigInteger('reason_id')->nullable()->index();
            $table->text('job_leave_specify')->nullable();
            $table->string('job_business')->nullable();
            $table->text('job_position')->nullable();
            $table->string('job_salary')->nullable();
            $table->string('job_reference_name')->nullable();
            $table->string('job_reference_phone')->nullable();
            $table->unsignedBigInteger('retrenchment_id')->nullable()->index();
            $table->text('job_retrenched_specify')->nullable();
            $table->unsignedBigInteger('previous_job_position_id')->nullable()->index();
            $table->text('job_shoprite_position_specify')->nullable();
            $table->text('job_shoprite_leave')->nullable();
            $table->unsignedBigInteger('transport_id')->nullable()->index();
            $table->text('transport_specify')->nullable();
            $table->unsignedBigInteger('disability_id')->nullable()->index();
            $table->text('illness_specify')->nullable();
            $table->date('commencement')->nullable();
            $table->unsignedBigInteger('type_id')->nullable()->index();
            $table->text('application_reason_specify')->nullable();
            $table->enum('relocate', ['Yes', 'No'])->nullable();
            $table->text('relocate_town')->nullable();
            $table->enum('vacancy', ['Yes', 'No'])->nullable();
            $table->enum('shift', ['Yes', 'No'])->nullable();
            $table->enum('has_bank_account', ['Yes', 'No'])->nullable();
            $table->unsignedBigInteger('bank_id')->nullable()->index();
            $table->string('bank_specify')->nullable();
            $table->string('bank_number', 20)->nullable();
            $table->text('expected_salary')->nullable();

            // Adding back the foreign keys
            $table->foreign('brand_id')->references('id')->on('brands')->onUpdate('CASCADE')->onDelete('SET NULL');
            $table->foreign('position_id')->references('id')->on('positions')->onUpdate('CASCADE')->onDelete('SET NULL');
            $table->foreign('reason_id')->references('id')->on('reasons')->onUpdate('CASCADE')->onDelete('SET NULL');
            $table->foreign('retrenchment_id')->references('id')->on('retrenchments')->onUpdate('CASCADE')->onDelete('SET NULL');
            $table->foreign('previous_job_position_id')->references('id')->on('positions')->onUpdate('CASCADE')->onDelete('SET NULL');
            $table->foreign('transport_id')->references('id')->on('transports')->onUpdate('CASCADE')->onDelete('SET NULL');
            $table->foreign('disability_id')->references('id')->on('disabilities')->onUpdate('CASCADE')->onDelete('SET NULL');
            $table->foreign('type_id')->references('id')->on('types')->onUpdate('CASCADE')->onDelete('SET NULL');
            $table->foreign('bank_id')->references('id')->on('banks')->onUpdate('CASCADE')->onDelete('SET NULL');
        });
    }
};
