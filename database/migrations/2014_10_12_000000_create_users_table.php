<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('firstname')->nullable();
            $table->string('lastname')->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('phone')->unique()->nullable();
            $table->string('password');
            $table->text('avatar')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('position_id')->nullable();
            $table->text('website')->nullable();
            $table->unsignedBigInteger('role_id')->nullable();
            $table->unsignedBigInteger('status_id')->nullable();
    
            $table->foreign('company_id')
                  ->references('id')
                  ->on('companies')
                  ->onUpdate('cascade')
                  ->onDelete('set null')
                  ->nullable();
            $table->foreign('position_id')
                  ->references('id')
                  ->on('positions')
                  ->onUpdate('cascade')
                  ->onDelete('set null')
                  ->nullable();           
            $table->foreign('role_id')
                  ->references('id')
                  ->on('roles')
                  ->onUpdate('cascade')
                  ->onDelete('set null');
            $table->foreign('status_id')
                  ->references('id')
                  ->on('status')
                  ->onUpdate('cascade')
                  ->onDelete('set null')
                  ->nullable();
            $table->rememberToken();
            $table->timestamps();
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
}
