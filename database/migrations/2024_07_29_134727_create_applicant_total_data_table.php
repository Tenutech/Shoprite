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
        Schema::create('applicant_total_data', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->year('year')->nullable()->unique('unique_year');
            $table->integer('total_applicants')->nullable()->default(0);
            $table->integer('total_interviewd')->nullable()->default(0);
            $table->integer('total_appointed')->nullable()->default(0);
            $table->integer('total_time_to_appointed')->nullable()->default(0);
            $table->integer('jan')->nullable()->default(0);
            $table->integer('feb')->nullable()->default(0);
            $table->integer('mar')->nullable()->default(0);
            $table->integer('apr')->nullable()->default(0);
            $table->integer('may')->nullable()->default(0);
            $table->integer('jun')->nullable()->default(0);
            $table->integer('jul')->nullable()->default(0);
            $table->integer('aug')->nullable()->default(0);
            $table->integer('sep')->nullable()->default(0);
            $table->integer('oct')->nullable()->default(0);
            $table->integer('nov')->nullable()->default(0);
            $table->integer('dec')->nullable()->default(0);
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('applicant_total_data');
    }
};
