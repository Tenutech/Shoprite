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
        Schema::table('interviews', function (Blueprint $table) {
            $table->foreign(['applicant_id'])->references(['id'])->on('applicants')->onUpdate('CASCADE')->onDelete('SET NULL');
            $table->foreign(['vacancy_id'])->references(['id'])->on('vacancies')->onUpdate('CASCADE')->onDelete('SET NULL');
            $table->foreign(['interviewer_id'])->references(['id'])->on('users')->onUpdate('CASCADE')->onDelete('SET NULL');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('interviews', function (Blueprint $table) {
            $table->dropForeign('interviews_applicant_id_foreign');
            $table->dropForeign('interviews_vacancy_id_foreign');
            $table->dropForeign('interviews_interviewer_id_foreign');
        });
    }
};
