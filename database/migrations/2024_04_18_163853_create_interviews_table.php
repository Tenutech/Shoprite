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
        Schema::create('interviews', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('applicant_id')->nullable()->index('interviews_applicant_id_foreign');
            $table->unsignedBigInteger('interviewer_id')->nullable()->index('interviews_interviewer_id_foreign');
            $table->unsignedBigInteger('vacancy_id')->nullable()->index('interviews_vacancy_id_foreign');
            $table->date('scheduled_date')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('location', 255)->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['Scheduled', 'Confirmed', 'Declined', 'Reschedule', 'Completed', 'Cancelled', 'No Show'])->nullable()->default('Scheduled');
            $table->string('score')->nullable();
            $table->dateTime('reschedule_date')->nullable();
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
        Schema::dropIfExists('interviews');
    }
};
