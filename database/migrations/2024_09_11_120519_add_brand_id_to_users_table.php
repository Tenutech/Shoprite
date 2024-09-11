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
        Schema::table('users', function (Blueprint $table) {
            // Adding the brand_id column after the region_id field
            $table->unsignedBigInteger('brand_id')->nullable()->after('region_id');

            // Setting up the foreign key constraint
            $table->foreign('brand_id')
                  ->references('id')
                  ->on('brands')
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
        Schema::table('users', function (Blueprint $table) {
            // Dropping the foreign key and the brand_id column
            $table->dropForeign(['brand_id']);
            $table->dropColumn('brand_id');
        });
    }
};
