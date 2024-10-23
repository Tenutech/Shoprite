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
        Schema::table('chats', function (Blueprint $table) {
            // Add the 'code' and 'reason' fields to store the error details when a message fails
            $table->integer('code')->nullable()->after('status'); // Error code (nullable in case of no failure)
            $table->text('reason')->nullable()->after('code');  // Reason for failure (nullable)
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('chats', function (Blueprint $table) {
            // Drop the 'code' and 'reason' fields
            $table->dropColumn(['code', 'reason']);
        });
    }
};
