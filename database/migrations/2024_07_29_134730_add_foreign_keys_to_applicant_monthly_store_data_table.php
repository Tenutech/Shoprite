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
        Schema::table('applicant_monthly_store_data', function (Blueprint $table) {
            $table->foreign(['applicant_monthly_data_id'])->references(['id'])->on('applicant_monthly_data')->onUpdate('CASCADE')->onDelete('SET NULL');
            $table->foreign(['store_id'])->references(['id'])->on('stores')->onUpdate('CASCADE')->onDelete('SET NULL');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('applicant_monthly_store_data', function (Blueprint $table) {
            $table->dropForeign('applicant_monthly_store_data_applicant_monthly_data_id_foreign');
            $table->dropForeign('applicant_monthly_store_data_store_id_foreign');
        });
    }
};
