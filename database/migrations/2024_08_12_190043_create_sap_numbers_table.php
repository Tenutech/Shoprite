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
        Schema::create('sap_numbers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vacancy_id');
            $table->string('position_detail', 8)->unique()->comment('8 digit numeric only, unique');
            $table->string('sap_number', 8)->comment('8 digit SAP position number');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->foreign('vacancy_id')->references('id')->on('vacancies')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sap_numbers');
    }
};