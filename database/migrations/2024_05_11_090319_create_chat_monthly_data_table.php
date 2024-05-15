<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChatMonthlyDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chat_monthly_data', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('chat_total_data_id')->unsigned()->nullable();
            $table->enum('chat_type', ['Incoming', 'Outgoing'])->collation('utf8mb4_general_ci')->nullable();
            $table->enum('month', ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'])->collation('utf8mb4_general_ci')->nullable();
            $table->integer('count')->default(0);
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('chat_total_data_id')->references('id')->on('chat_total_data')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('chat_monthly_data');
    }
}
