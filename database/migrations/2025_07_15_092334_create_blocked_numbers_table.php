<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations to create the blocked_numbers table.
     */
    public function up(): void
    {
        Schema::create('blocked_numbers', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key

            $table->string('phone_number')->unique(); 
            // Phone number in international format (e.g., +27871234567)
            // Unique to prevent duplicate blocks

            $table->string('reason')->nullable();     
            // Optional column to store a reason or note for blocking the number

            $table->timestamps(); 
            // Adds 'created_at' and 'updated_at' columns automatically
        });
    }

    /**
     * Reverse the migrations (used when rolling back).
     */
    public function down(): void
    {
        Schema::dropIfExists('blocked_numbers'); 
        // Deletes the table if it exists
    }
};
