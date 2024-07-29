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
        Schema::create('chat_monthly_data', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('chat_total_data_id')->nullable()->index();
            $table->enum('chat_type', ['Incoming', 'Outgoing'])->nullable()->collation('utf8mb4_general_ci');
            $table->enum('month', [
                'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
            ])->nullable()->collation('utf8mb4_general_ci');
            $table->integer('count')->default(0)->nullable();
            $table->timestamp('created_at')->useCurrent()->nullable();
            $table->timestamp('updated_at')->nullable();

            // Add foreign key constraint
            $table->foreign('chat_total_data_id')
                ->references('id')
                ->on('chat_total_data')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('chat_monthly_data', function (Blueprint $table) {
            // Drop foreign key constraint first
            $table->dropForeign(['chat_total_data_id']);
        });

        Schema::dropIfExists('chat_monthly_data');
    }
};