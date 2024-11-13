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
        // Create the 'query_categories' table
        Schema::create('query_categories', function (Blueprint $table) {
            // Adding an auto-incrementing unsigned integer as the primary key
            $table->id();
            
            // Adding 'name' column with type VARCHAR and a max length of 255 characters
            $table->string('name', 255);

            // Adding 'description' column with type TEXT to store additional details about the category
            // This column can hold a longer description, providing context or examples for each category
            $table->text('description')->nullable();

            // Adding 'severity' column with ENUM type, with allowed values: 'Low', 'High', 'Critical'
            // ENUM is used here to restrict the values that can be inserted into this field
            $table->enum('severity', ['Low', 'High', 'Critical']);
            
            // Adding timestamps for created_at and updated_at columns to track record creation and updates
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Drops the 'query_categories' table if it exists
        Schema::dropIfExists('query_categories');
    }
};
