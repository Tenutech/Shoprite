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
        Schema::table('chat_templates', function (Blueprint $table) {
            $table->foreign(['state_id'], 'message_templates_state_id_foreign')->references(['id'])->on('states')->onUpdate('CASCADE')->onDelete('SET NULL');
            $table->foreign(['category_id'], 'message_templates_category_id_foreign')->references(['id'])->on('chat_categories')->onUpdate('CASCADE')->onDelete('SET NULL');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('chat_templates', function (Blueprint $table) {
            $table->dropForeign('message_templates_state_id_foreign');
            $table->dropForeign('message_templates_category_id_foreign');
        });
    }
};
