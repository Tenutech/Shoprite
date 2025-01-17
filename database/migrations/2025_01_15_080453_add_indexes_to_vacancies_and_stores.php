<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexesToVacanciesAndStores extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vacancies', function (Blueprint $table) {
            $table->index('created_at', 'vacancies_created_at_index');
            $table->index('store_id', 'vacancies_store_id_index');
        });

        Schema::table('stores', function (Blueprint $table) {
            $table->index(['division_id', 'region_id'], 'stores_division_region_index');
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
            $table->dropIndex('vacancies_created_at_index');
            $table->dropIndex('vacancies_store_id_index');
        });

        Schema::table('stores', function (Blueprint $table) {
            $table->dropIndex('stores_division_region_index');
        });
    }
}