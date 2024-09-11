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
        // Add division_id to users table after region_id
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('division_id')
                ->nullable()
                ->after('region_id')
                ->constrained('divisions')
                ->onUpdate('cascade')
                ->onDelete('set null');
        });

        // Add division_id to stores table after region_id
        Schema::table('stores', function (Blueprint $table) {
            $table->foreignId('division_id')
                ->nullable()
                ->after('region_id')
                ->constrained('divisions')
                ->onUpdate('cascade')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Drop division_id from users table
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['division_id']);
            $table->dropColumn('division_id');
        });

        // Drop division_id from stores table
        Schema::table('stores', function (Blueprint $table) {
            $table->dropForeign(['division_id']);
            $table->dropColumn('division_id');
        });
    }
};
