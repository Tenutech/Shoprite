<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        if (!Schema::hasColumn('vacancies', 'auto_deleted')) {
            Schema::table('vacancies', function (Blueprint $table) {
                $table->enum('auto_deleted', ['Yes', 'No'])->default('No')->nullable()->after('deleted');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        if (Schema::hasColumn('vacancies', 'auto_deleted')) {
            Schema::table('vacancies', function (Blueprint $table) {
                $table->dropColumn('auto_deleted');
            });
        }
    }
};
