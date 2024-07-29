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
        Schema::table('applicant_speak_languages', function (Blueprint $table) {
            $table->foreign(['applicant_id'])->references(['id'])->on('applicants')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign(['language_id'])->references(['id'])->on('languages')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('applicant_speak_languages', function (Blueprint $table) {
            $table->dropForeign('applicant_speak_languages_applicant_id_foreign');
            $table->dropForeign('applicant_speak_languages_language_id_foreign');
        });
    }
};
