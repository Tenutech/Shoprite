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
        Schema::create('notifications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->nullable()->index('notification_user_id_foreign');
            $table->unsignedBigInteger('causer_id')->nullable()->index('notification_causer_id_foreign');
            $table->string('subject_type', 190)->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->unsignedBigInteger('type_id')->nullable()->index('notification_type_id_foreign');
            $table->text('notification')->nullable();
            $table->enum('read', ['Yes', 'No'])->nullable()->default('No');
            $table->enum('show', ['Yes', 'No'])->nullable()->default('Yes');
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
        Schema::dropIfExists('notifications');
    }
};
