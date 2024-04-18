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
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('firstname')->nullable();
            $table->string('lastname')->nullable();
            $table->string('email')->nullable()->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('phone')->nullable()->unique();
            $table->string('id_number', 13)->nullable();
            $table->enum('id_verified', ['Yes', 'No'])->nullable();
            $table->string('password');
            $table->text('avatar')->nullable();
            $table->date('birth_date')->nullable();
            $table->integer('age')->nullable();
            $table->unsignedBigInteger('gender_id')->nullable()->index('users_genders_id_foreign');
            $table->integer('resident')->nullable();
            $table->unsignedBigInteger('company_id')->nullable()->index('users_company_id_foreign');
            $table->unsignedBigInteger('position_id')->nullable()->index('users_position_id_foreign');
            $table->unsignedBigInteger('role_id')->nullable()->index('users_role_id_foreign');
            $table->unsignedBigInteger('applicant_id')->nullable()->index('users_applicant_id_foreign');
            $table->unsignedBigInteger('store_id')->nullable()->index('users_store_id_foreign');
            $table->integer('internal')->nullable();
            $table->unsignedBigInteger('status_id')->nullable()->index('users_status_id_foreign');
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
};
