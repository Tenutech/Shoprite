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
        Schema::create('signers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('signature_file_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('email');
            $table->enum('status', ['pending', 'signed', 'declined'])->default('pending');
            $table->string('signature_path')->nullable();
            $table->timestamp('signed_at')->nullable();
            $table->foreign('signature_file_id')->references('id')->on('signature_files')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('signers');
    }
};