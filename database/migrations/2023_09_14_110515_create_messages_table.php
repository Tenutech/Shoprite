<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('from_id')->nullable();;
            $table->unsignedBigInteger('to_id')->nullable();;
            $table->text('message')->nullable();            
            $table->enum('read', ['Yes', 'No'])->default('No');
            $table->timestamps();

            $table->foreign('from_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('set null');
            $table->foreign('to_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
