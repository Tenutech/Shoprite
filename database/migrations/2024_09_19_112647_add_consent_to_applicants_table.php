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
            // Add the consent column as an ENUM with Yes and No, after education_id
            $table->enum('consent', ['Yes', 'No'])->nullable()->default(null)->after('education_id');
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
            // Drop the consent column if we roll back the migration
            $table->dropColumn('consent');
        });
    }
};
