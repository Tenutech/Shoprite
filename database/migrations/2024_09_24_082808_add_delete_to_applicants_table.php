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
            // Add the 'delete' column as an enum with values 'Yes' and 'No'
            // It's nullable and has a default value of null, positioned after 'no_show'
            $table->enum('user_delete', ['Yes', 'No'])->nullable()->default(null)->after('no_show');
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
            // Drop the 'delete' column if the migration is rolled back
            $table->dropColumn('delete');
        });
    }
};
