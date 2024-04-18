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
        Schema::create('vacancy_fills', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('vacancy_id')->index('vacancy_fills_vacancy_id_foreign');
            $table->unsignedBigInteger('applicant_id')->index('vacancy_fills_applicant_id_foreign');
            $table->enum('approved', ['Yes', 'Pending', 'No'])->nullable()->default('Pending');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vacancy_fills');
    }
};
