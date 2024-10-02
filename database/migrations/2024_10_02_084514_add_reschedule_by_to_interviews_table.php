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
        Schema::table('interviews', function (Blueprint $table) {
            // Add the 'reschedule_by' column as an enum after 'reschedule_date'
            $table->enum('reschedule_by', ['Applicant', 'Manager'])
                  ->after('reschedule_date')
                  ->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('interviews', function (Blueprint $table) {
            // Remove the 'reschedule_by' column if rolling back
            $table->dropColumn('reschedule_by');
        });
    }
};
