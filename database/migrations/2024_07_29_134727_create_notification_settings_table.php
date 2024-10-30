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
        Schema::create('notification_settings', function (Blueprint $table) {
            $table->integer('id', true);
            $table->unsignedBigInteger('user_id')->nullable()->index('notification_settings_user_id_foreign');
            $table->boolean('receive_email_notifications')->nullable()->default(true);
            $table->boolean('receive_whatsapp_notifications')->nullable()->default(true);
            $table->boolean('notify_application_submitted')->nullable()->default(true);
            $table->boolean('notify_application_status')->nullable()->default(false);
            $table->boolean('notify_shortlisted')->nullable()->default(false);
            $table->boolean('notify_interview')->nullable()->default(true);
            $table->boolean('notify_vacancy_status')->nullable()->default(false);
            $table->boolean('notify_new_application')->nullable()->default(false);
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notification_settings');
    }
};
