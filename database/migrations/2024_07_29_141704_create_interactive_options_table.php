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
        Schema::create('interactive_options', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('chat_template_id')->nullable();
            $table->string('title', 255)->nullable();
            $table->string('description', 255)->nullable();
            $table->tinyInteger('value')->nullable();
            $table->timestamps();

            // Add foreign key constraint
            $table->foreign('chat_template_id')->references('id')->on('chat_templates')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('interactive_options', function (Blueprint $table) {
            // Drop foreign key constraint first
            $table->dropForeign(['chat_template_id']);
        });

        Schema::dropIfExists('interactive_options');
    }
};