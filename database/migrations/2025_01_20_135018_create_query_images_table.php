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
        if (!Schema::hasTable('query_images')) { // Check if the table doesn't exist
            Schema::create('query_images', function (Blueprint $table) {
                $table->id(); // Primary key
                $table->foreignId('query_id') // Foreign key referencing queries table
                      ->constrained('queries')
                      ->onUpdate('cascade') // Update cascade
                      ->onDelete('cascade'); // Delete cascade
                $table->string('url'); // Field to store the URL of the image
                $table->timestamps(); // Created_at and updated_at timestamps
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('query_images');
    }
};
