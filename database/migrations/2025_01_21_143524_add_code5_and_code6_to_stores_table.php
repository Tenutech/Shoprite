<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('stores', 'code_5')) {
            DB::statement("ALTER TABLE `stores` ADD `code_5` VARCHAR(50) NULL AFTER `code`");
        }

        if (!Schema::hasColumn('stores', 'code_6')) {
            DB::statement("ALTER TABLE `stores` ADD `code_6` VARCHAR(50) NULL AFTER `code_5`");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('stores', 'code_5')) {
            Schema::table('stores', function (Blueprint $table) {
                $table->dropColumn('code_5');
            });
        }

        if (Schema::hasColumn('stores', 'code_6')) {
            Schema::table('stores', function (Blueprint $table) {
                $table->dropColumn('code_6');
            });
        }
    }
};
