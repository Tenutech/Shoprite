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
        Schema::create('applicant_checks', function (Blueprint $table) {
            $table->bigInteger('id', true);
            $table->unsignedBigInteger('applicant_id')->nullable()->index('applicant_checks_applicant_id_foreign');
            $table->unsignedBigInteger('check_id')->nullable()->index('applicant_checks_check_id_foreign');
            $table->enum('result', ['Passed', 'Failed', 'Discrepancy'])->nullable()->default('Failed');
            $table->text('reason')->nullable();
            $table->string('file')->nullable();
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
        Schema::dropIfExists('applicant_checks');
    }
};
