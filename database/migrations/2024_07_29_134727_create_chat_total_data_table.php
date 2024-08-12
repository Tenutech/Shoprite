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
        // Drop the table if it exists to avoid any conflicts
        Schema::dropIfExists('chat_total_data');
        

        // Create the table
        Schema::create('chat_total_data', function (Blueprint $table) {
            $table->bigIncrements('id');
            
            $table->year('year')->nullable()->unique();
            $table->integer('total_incoming')->nullable()->default(0);
            $table->integer('total_outgoing')->nullable()->default(0);
            $table->integer('jan_incoming')->nullable()->default(0);
            $table->integer('jan_outgoing')->nullable()->default(0);
            $table->integer('feb_incoming')->nullable()->default(0);
            $table->integer('feb_outgoing')->nullable()->default(0);
            $table->integer('mar_incoming')->nullable()->default(0);
            $table->integer('mar_outgoing')->nullable()->default(0);
            $table->integer('apr_incoming')->nullable()->default(0);
            $table->integer('apr_outgoing')->nullable()->default(0);
            $table->integer('may_incoming')->nullable()->default(0);
            $table->integer('may_outgoing')->nullable()->default(0);
            $table->integer('jun_incoming')->nullable()->default(0);
            $table->integer('jun_outgoing')->nullable()->default(0);
            $table->integer('jul_incoming')->default(0);
            $table->integer('jul_outgoing')->default(0);
            $table->integer('aug_incoming')->default(0);
            $table->integer('aug_outgoing')->default(0);
            $table->integer('sep_incoming')->default(0);
            $table->integer('sep_outgoing')->default(0);
            $table->integer('oct_incoming')->default(0);
            $table->integer('oct_outgoing')->default(0);
            $table->integer('nov_incoming')->default(0);
            $table->integer('nov_outgoing')->default(0);
            $table->integer('dec_incoming')->default(0);
            $table->integer('dec_outgoing')->default(0);
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('chat_total_data');
    }
};