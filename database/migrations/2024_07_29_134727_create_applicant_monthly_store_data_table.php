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
        Schema::create('applicant_monthly_store_data', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('store_id')->nullable()->index('applicant_monthly_store_data_store_id_foreign');
            $table->unsignedBigInteger('applicant_monthly_data_id')->nullable()->index('applicant_monthly_store_data_applicant_monthly_data_id_foreign');
            $table->integer('count')->nullable()->default(0);
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
        Schema::dropIfExists('applicant_monthly_store_data');
    }
};
