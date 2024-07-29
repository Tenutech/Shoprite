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
        Schema::table('vacancies', function (Blueprint $table) {
            $table->foreign(['position_id'])->references(['id'])->on('positions')->onUpdate('CASCADE')->onDelete('SET NULL');
            $table->foreign(['store_id'])->references(['id'])->on('stores')->onUpdate('CASCADE')->onDelete('SET NULL');
            $table->foreign(['user_id'])->references(['id'])->on('users')->onUpdate('CASCADE')->onDelete('SET NULL');
            $table->foreign(['status_id'])->references(['id'])->on('vacancy_status')->onUpdate('CASCADE')->onDelete('SET NULL');
            $table->foreign(['type_id'])->references(['id'])->on('types')->onUpdate('CASCADE')->onDelete('SET NULL');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vacancies', function (Blueprint $table) {
            $table->dropForeign('vacancies_position_id_foreign');
            $table->dropForeign('vacancies_store_id_foreign');
            $table->dropForeign('vacancies_user_id_foreign');
            $table->dropForeign('vacancies_status_id_foreign');
            $table->dropForeign('vacancies_type_id_foreign');
        });
    }
};
