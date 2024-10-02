<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (DB::getDriverName() !== 'sqlite') {
            // For MySQL or other databases
            DB::statement("ALTER TABLE interviews MODIFY COLUMN status ENUM('Scheduled', 'Confirmed', 'Declined', 'Reschedule', 'Completed', 'Cancelled', 'No Show', 'Appointed', 'Regretted') NULL");
        } else {
            // For SQLite, recreate the column
            Schema::table('interviews', function (Blueprint $table) {
                $table->text('status_new')->nullable(); // Create a new nullable text column for status
            });

            // Copy the data from the old column to the new column
            DB::table('interviews')->update(['status_new' => DB::raw('status')]);

            // Drop the old column
            Schema::table('interviews', function (Blueprint $table) {
                $table->dropColumn('status');
            });

            // Rename the new column to 'status'
            Schema::table('interviews', function (Blueprint $table) {
                $table->renameColumn('status_new', 'status');
            });

            // Since SQLite doesn't support enums, you can leave the column as text and handle the enum logic in the application layer
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (DB::getDriverName() !== 'sqlite') {
            // For MySQL or other databases
            DB::statement("ALTER TABLE interviews MODIFY COLUMN status ENUM('Scheduled', 'Confirmed', 'Declined', 'Reschedule', 'Completed', 'Cancelled', 'No Show') NULL");
        } else {
            // For SQLite (reverse the changes)
            Schema::table('interviews', function (Blueprint $table) {
                $table->text('status_new')->nullable(); // Create a new column to store the original data
            });

            DB::table('interviews')->update(['status_new' => DB::raw('status')]);

            Schema::table('interviews', function (Blueprint $table) {
                $table->dropColumn('status');
            });

            Schema::table('interviews', function (Blueprint $table) {
                $table->renameColumn('status_new', 'status');
            });
        }
    }
};