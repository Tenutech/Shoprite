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
        Schema::table('emails', function (Blueprint $table) {
            $table->foreign(['template_id'], 'email_template_id_foreign')->references(['id'])->on('email_templates')->onUpdate('CASCADE')->onDelete('SET NULL');
            $table->foreign(['user_id'], 'email_user_id_foreign')->references(['id'])->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('emails', function (Blueprint $table) {
            $table->dropForeign('email_template_id_foreign');
            $table->dropForeign('email_user_id_foreign');
        });
    }
};
