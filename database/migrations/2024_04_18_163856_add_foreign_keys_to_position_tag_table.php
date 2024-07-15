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
        Schema::table('position_tag', function (Blueprint $table) {
            $table->foreign(['tag_id'])->references(['id'])->on('tags')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign(['position_id'])->references(['id'])->on('positions')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('position_tag', function (Blueprint $table) {
            $table->dropForeign('position_tag_tag_id_foreign');
            $table->dropForeign('position_tag_position_id_foreign');
        });
    }
};
