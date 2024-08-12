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
        Schema::create('reminder_settings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('type', 255);
            $table->unsignedBigInteger('role_id')->nullable()->index();
            $table->integer('delay');
            $table->unsignedBigInteger('email_template_id')->nullable()->index('reminders_email_template_id_foreign');
            $table->boolean('is_active')->nullable()->default(true);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reminder_settings');
    }
};
