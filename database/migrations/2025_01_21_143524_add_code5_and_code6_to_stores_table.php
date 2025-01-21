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
        Schema::table('stores', function (Blueprint $table) {
            if (!Schema::hasColumn('stores', 'code_5')) {
                $table->string('code_5', 50)->nullable()->after('code');
            }

            if (!Schema::hasColumn('stores', 'code_6')) {
                $table->string('code_6', 50)->nullable()->after('code_5');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('stores', function (Blueprint $table) {
            if (Schema::hasColumn('stores', 'code_5')) {
                $table->dropColumn('code_5');
            }

            if (Schema::hasColumn('stores', 'code_6')) {
                $table->dropColumn('code_6');
            }
        });
    }
};
