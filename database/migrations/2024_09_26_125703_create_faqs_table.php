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
        Schema::create('faqs', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key
            $table->string('name'); // Name of the FAQ
            $table->text('description'); // Detailed description of the FAQ
            $table->unsignedBigInteger('role_id')->nullable(); // Role ID that should see this FAQ
            $table->enum('type', ['Account', 'General']); // Type (Account or General)
            $table->timestamps(); // created_at and updated_at timestamps

            // Define foreign key for role_id referencing the roles table
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('faqs');
    }
};
