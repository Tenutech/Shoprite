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
        Schema::table('users', function (Blueprint $table) {
            $table->foreign(['role_id'])->references(['id'])->on('roles')->onUpdate('CASCADE')->onDelete('SET NULL');
            $table->foreign(['store_id'])->references(['id'])->on('stores')->onUpdate('CASCADE')->onDelete('SET NULL');
            $table->foreign(['company_id'])->references(['id'])->on('companies')->onUpdate('CASCADE')->onDelete('SET NULL');
            $table->foreign(['position_id'])->references(['id'])->on('positions')->onUpdate('CASCADE')->onDelete('SET NULL');
            $table->foreign(['status_id'])->references(['id'])->on('status')->onUpdate('CASCADE')->onDelete('SET NULL');
            $table->foreign(['applicant_id'])->references(['id'])->on('applicants')->onUpdate('CASCADE')->onDelete('SET NULL');
            $table->foreign(['gender_id'], 'users_genders_id_foreign')->references(['id'])->on('genders')->onUpdate('CASCADE')->onDelete('SET NULL');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign('users_role_id_foreign');
            $table->dropForeign('users_store_id_foreign');
            $table->dropForeign('users_company_id_foreign');
            $table->dropForeign('users_position_id_foreign');
            $table->dropForeign('users_status_id_foreign');
            $table->dropForeign('users_applicant_id_foreign');
            $table->dropForeign('users_genders_id_foreign');
        });
    }
};
