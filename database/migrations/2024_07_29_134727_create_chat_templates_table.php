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
        Schema::create('chat_templates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('message')->nullable();
            $table->unsignedBigInteger('state_id')->nullable()->index('message_templates_state_id_foreign');
            $table->unsignedBigInteger('category_id')->nullable()->index('message_templates_category_id_foreign');
            $table->string('answer', 1)->nullable();
            $table->tinyInteger('sort')->nullable();
            $table->string('template')->nullable();
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
        Schema::dropIfExists('chat_templates');
    }
};
