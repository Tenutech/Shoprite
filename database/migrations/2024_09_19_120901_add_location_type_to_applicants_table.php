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
        Schema::table('applicants', function (Blueprint $table) {
            // Add the location_type column as an ENUM with Address and Pin, after brand_id
            $table->enum('location_type', ['Address', 'Pin'])->nullable()->default(null)->after('brand_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('applicants', function (Blueprint $table) {
            // Drop the location_type column if we roll back the migration
            $table->dropColumn('location_type');
        });
    }
};
