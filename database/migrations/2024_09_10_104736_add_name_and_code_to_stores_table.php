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
        Schema::table('stores', function (Blueprint $table) {
            // Add name field before brand_id
            $table->string('name', 255)->nullable()->after('id');
            
            // Add code field after division_id
            $table->string('code', 50)->nullable()->after('division_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('stores', function (Blueprint $table) {
            // Drop name and code fields
            $table->dropColumn('name');
            $table->dropColumn('code');
        });
    }
};
