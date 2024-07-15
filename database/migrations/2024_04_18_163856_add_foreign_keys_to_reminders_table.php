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
        Schema::table('reminders', function (Blueprint $table) {
            $table->foreign(['reminder_setting_id'])->references(['id'])->on('reminder_settings')->onUpdate('CASCADE')->onDelete('SET NULL');
            $table->foreign(['email_template_id'])->references(['id'])->on('email_templates')->onUpdate('CASCADE')->onDelete('SET NULL');
            $table->foreign(['user_id'])->references(['id'])->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reminders', function (Blueprint $table) {
            $table->dropForeign('reminders_reminder_setting_id_foreign');
            $table->dropForeign('reminders_email_template_id_foreign');
            $table->dropForeign('reminders_user_id_foreign');
        });
    }
};
