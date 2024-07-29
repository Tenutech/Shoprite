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
        Schema::create('applicant_monthly_data', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('applicant_total_data_id')->nullable()->index('applicant_monthly_data_applicant_total_data_id_foreign');
            $table->integer('category_id')->nullable();
            $table->enum('category_type', ['Gender', 'Race', 'Position', 'Province', 'Application', 'Interviewed', 'Appointed', 'Rejected'])->nullable();
            $table->enum('month', ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'])->nullable();
            $table->integer('count')->nullable()->default(0);
            $table->integer('total_time_to_appointed')->nullable();
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
        Schema::dropIfExists('applicant_monthly_data');
    }
};
