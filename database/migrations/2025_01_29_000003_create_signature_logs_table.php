<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('signature_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('signature_file_id');
            $table->string('event');
            $table->string('user_email');
            $table->ipAddress('ip_address');
            $table->timestamp('timestamp')->useCurrent();
            $table->foreign('signature_file_id')->references('id')->on('signature_files')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('signature_logs');
    }
};
