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
        Schema::table('applicant_checks', function (Blueprint $table) {
            $table->foreign(['applicant_id'])->references(['id'])->on('applicants')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign(['check_id'])->references(['id'])->on('checks')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('applicant_checks', function (Blueprint $table) {
            $table->dropForeign('applicant_checks_applicant_id_foreign');
            $table->dropForeign('applicant_checks_check_id_foreign');
        });
    }
};
