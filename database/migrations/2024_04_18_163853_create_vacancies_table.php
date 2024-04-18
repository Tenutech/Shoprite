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
        Schema::create('vacancies', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->nullable()->index('vacancies_user_id_foreign');
            $table->unsignedBigInteger('position_id')->nullable()->index('vacancies_position_id_foreign');
            $table->unsignedBigInteger('store_id')->nullable()->index('vacancies_store_id_foreign');
            $table->unsignedBigInteger('type_id')->nullable()->index('vacancies_type_id_foreign');
            $table->unsignedBigInteger('status_id')->nullable()->index('vacancies_status_id_foreign');
            $table->unsignedInteger('open_positions')->nullable()->default(0);
            $table->unsignedInteger('filled_positions')->nullable()->default(0);
            $table->enum('advertisement', ['Any', 'External', 'Internal'])->nullable()->default('Any');
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
        Schema::dropIfExists('vacancies');
    }
};
