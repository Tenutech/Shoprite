<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('downloads', function (Blueprint $table) {
            $table->id();
            $table->string('file_name')->nullable();
            $table->string('file_path')->nullable();
            $table->string('type');
            $table->string('status')->default('pending');
            $table->integer('progress')->default(0);
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('meta')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('download_model');
    }
};
