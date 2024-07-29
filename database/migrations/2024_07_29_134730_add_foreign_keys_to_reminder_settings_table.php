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
        Schema::table('reminder_settings', function (Blueprint $table) {
            $table->foreign(['email_template_id'])->references(['id'])->on('email_templates')->onUpdate('CASCADE')->onDelete('SET NULL');
            $table->foreign(['role_id'])->references(['id'])->on('roles')->onUpdate('CASCADE')->onDelete('SET NULL');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reminder_settings', function (Blueprint $table) {
            $table->dropForeign('reminder_settings_email_template_id_foreign');
            $table->dropForeign('reminder_settings_role_id_foreign');
        });
    }
};
