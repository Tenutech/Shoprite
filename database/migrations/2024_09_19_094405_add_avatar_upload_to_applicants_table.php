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
            // Add the avatar_upload column as an ENUM with default null
            $table->enum('avatar_upload', ['Yes', 'No'])->nullable()->default(null)->after('race_id');
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
            // Drop the avatar_upload column if we roll back the migration
            $table->dropColumn('avatar_upload');
        });
    }
};
