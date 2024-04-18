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
        Schema::table('applicant_monthly_data', function (Blueprint $table) {
            $table->foreign(['applicant_total_data_id'])->references(['id'])->on('applicant_total_data')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('applicant_monthly_data', function (Blueprint $table) {
            $table->dropForeign('applicant_monthly_data_applicant_total_data_id_foreign');
        });
    }
};
