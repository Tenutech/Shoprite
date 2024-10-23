<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This method adds three new columns (message_id, status, and template)
     * to the chats table after the 'type_id' column.
     * - 'message_id': Stores the unique ID of the WhatsApp message, can be null.
     * - 'status': Tracks the status of the message with possible values (Sent, Delivered, Read, Received, Failed), default is null.
     * - 'template': Stores the name of the template used to send the message, can be null.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('chats', function (Blueprint $table) {
            // Add 'message_id' column as a VARCHAR(191), nullable, positioned after 'type_id'
            $table->string('message_id', 191)->nullable()->after('type_id');

            // Add 'status' column as an ENUM with values (Sent, Delivered, Read, Received, Failed), default null
            $table->enum('status', ['Sent', 'Delivered', 'Read', 'Received', 'Failed'])->nullable()->after('message_id');

            // Add 'template' column as a VARCHAR(191), nullable, positioned after 'status'
            $table->string('template', 191)->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * This method removes the columns (message_id, status, and template) that were added in the 'up' method.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('chats', function (Blueprint $table) {
            // Drop the 'message_id', 'status', and 'template' columns if the migration is rolled back
            $table->dropColumn(['message_id', 'status', 'template']);
        });
    }
};
