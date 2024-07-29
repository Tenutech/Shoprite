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
        Schema::table('chats', function (Blueprint $table) {
            $table->foreign(['applicant_id'], 'messages_applicant_id_foreign')->references(['id'])->on('applicants')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign(['type_id'], 'messages_type_id_foreign')->references(['id'])->on('chat_types')->onUpdate('CASCADE')->onDelete('SET NULL');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('chats', function (Blueprint $table) {
            $table->dropForeign('messages_applicant_id_foreign');
            $table->dropForeign('messages_type_id_foreign');
        });
    }
};
