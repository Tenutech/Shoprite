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
        // Get the Doctrine Schema Manager to list current indexes.
        $schemaManager = Schema::getConnection()->getDoctrineSchemaManager();

        // --- For the stores table ---
        $storesIndexes = $schemaManager->listTableIndexes('stores');
        // Check if a normal index on the 'coordinates' column already exists.
        if (!array_key_exists('stores_coordinates_index', $storesIndexes)) {
            Schema::table('stores', function (Blueprint $table) {
                // Add a normal index (not a spatial index) on the coordinates column.
                $table->index('coordinates', 'stores_coordinates_index');
            });
        }

        // --- For the applicants table ---
        $applicantsIndexes = $schemaManager->listTableIndexes('applicants');
        // Check if a normal index on the 'coordinates' column already exists.
        if (!array_key_exists('applicants_coordinates_index', $applicantsIndexes)) {
            Schema::table('applicants', function (Blueprint $table) {
                // Add a normal index (not a spatial index) on the coordinates column.
                $table->index('coordinates', 'applicants_coordinates_index');
            });
        }
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
