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
        Schema::table('vacancy_fills', function (Blueprint $table) {
            $table->foreign(['applicant_id'])->references(['id'])->on('applicants')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign(['vacancy_id'])->references(['id'])->on('vacancies')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vacancy_fills', function (Blueprint $table) {
            $table->dropForeign('vacancy_fills_applicant_id_foreign');
            $table->dropForeign('vacancy_fills_vacancy_id_foreign');
        });
    }
};
