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
        // Add region_id to users table after store_id
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('region_id')
                ->nullable()
                ->after('store_id')
                ->constrained('regions')
                ->onUpdate('cascade')
                ->onDelete('set null');
        });

        // Add region_id to stores table after coordinates
        Schema::table('stores', function (Blueprint $table) {
            $table->foreignId('region_id')
                ->nullable()
                ->after('coordinates')
                ->constrained('regions')
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
        // Drop region_id from users table
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['region_id']);
            $table->dropColumn('region_id');
        });

        // Drop region_id from stores table
        Schema::table('stores', function (Blueprint $table) {
            $table->dropForeign(['region_id']);
            $table->dropColumn('region_id');
        });
    }
};