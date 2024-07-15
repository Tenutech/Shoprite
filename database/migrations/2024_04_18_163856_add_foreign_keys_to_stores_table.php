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
            $table->foreign(['town_id'])->references(['id'])->on('towns')->onUpdate('CASCADE')->onDelete('SET NULL');
            $table->foreign(['brand_id'])->references(['id'])->on('brands')->onUpdate('CASCADE')->onDelete('SET NULL');
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
            $table->dropForeign('stores_town_id_foreign');
            $table->dropForeign('stores_brand_id_foreign');
        });
    }
};
