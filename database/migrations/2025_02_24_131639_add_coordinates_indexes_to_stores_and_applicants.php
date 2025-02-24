<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds normal indexes on the 'coordinates' field for the 'stores' and 'applicants' tables,
     * if the indexes do not already exist.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->index('coordinates', 'stores_coordinates_index');
        });

        Schema::table('applicants', function (Blueprint $table) {
            $table->index('coordinates', 'applicants_coordinates_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * Drops the indexes on the 'coordinates' field for the 'stores' and 'applicants' tables.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropIndex('stores_coordinates_index');
        });

        Schema::table('applicants', function (Blueprint $table) {
            $table->dropIndex('applicants_coordinates_index');
        });
    }
};