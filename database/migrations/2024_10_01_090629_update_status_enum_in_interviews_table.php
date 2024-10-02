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
            DB::statement("ALTER TABLE interviews MODIFY COLUMN status ENUM('Scheduled', 'Confirmed', 'Declined', 'Reschedule', 'Completed', 'Cancelled', 'No Show', 'Appointed', 'Regretted') NOT NULL");
        } else {
            // For SQLite
            Schema::table('interviews', function (Blueprint $table) {
                $table->text('status_new')->nullable();
            });

            // Copy the data from the old column to the new one (this is optional, depending on your needs)
            DB::table('interviews')->update(['status_new' => DB::raw('status')]);

            // Drop the old column
            Schema::table('interviews', function (Blueprint $table) {
                $table->dropColumn('status');
            });

            // Rename the new column to 'status'
            Schema::table('interviews', function (Blueprint $table) {
                $table->renameColumn('status_new', 'status');
            });

            // Apply the constraints for the enum manually (SQLite doesn't support enums, so use check constraints)
            DB::statement("UPDATE interviews SET status = 'Scheduled' WHERE status IS NULL");
            DB::statement("ALTER TABLE interviews ADD CONSTRAINT check_status CHECK (status IN ('Scheduled', 'Confirmed', 'Declined', 'Reschedule', 'Completed', 'Cancelled', 'No Show', 'Appointed', 'Regretted'))");
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
            DB::statement("ALTER TABLE interviews MODIFY COLUMN status ENUM('Scheduled', 'Confirmed', 'Declined', 'Reschedule', 'Completed', 'Cancelled', 'No Show') NOT NULL");
        } else {
            // For SQLite (reverse the changes)
            Schema::table('interviews', function (Blueprint $table) {
                $table->text('status_new')->nullable();
            });

            DB::table('interviews')->update(['status_new' => DB::raw('status')]);

            Schema::table('interviews', function (Blueprint $table) {
                $table->dropColumn('status');
            });

            Schema::table('interviews', function (Blueprint $table) {
                $table->renameColumn('status_new', 'status');
            });

            DB::statement("UPDATE interviews SET status = 'Scheduled' WHERE status IS NULL");
            DB::statement("ALTER TABLE interviews ADD CONSTRAINT check_status CHECK (status IN ('Scheduled', 'Confirmed', 'Declined', 'Reschedule', 'Completed', 'Cancelled', 'No Show'))");
        }
    }
};
