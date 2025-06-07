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
    public function up(): void
    {
        Schema::create('signature_files', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('user_id');
            $table->string('file_path');
            $table->enum('status', ['prepared', 'sent', 'signed', 'declined'])->default('prepared');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('signature_files');
    }
};