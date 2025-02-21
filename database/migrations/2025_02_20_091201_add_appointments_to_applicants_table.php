<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        if (!Schema::hasColumn('applicants', 'appointments')) {
            Schema::table('applicants', function (Blueprint $table) {
                $table->json('appointments')->nullable()->after('appointed_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        if (Schema::hasColumn('applicants', 'appointments')) {
            Schema::table('applicants', function (Blueprint $table) {
                $table->dropColumn('appointments');
            });
        }
    }
};
