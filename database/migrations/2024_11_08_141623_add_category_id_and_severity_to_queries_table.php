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
        Schema::table('queries', function (Blueprint $table) {
            // Adding 'category_id' as an unsigned integer, referencing the 'id' column on 'query_categories' table
            $table->unsignedBigInteger('category_id')->nullable()->after('body');
            
            // Defining the foreign key constraint on 'category_id' that references 'id' on 'query_categories'
            $table->foreign('category_id')->references('id')->on('query_categories')->onDelete('set null')->onUpdate('cascade');

            // Adding 'severity' as an ENUM field with values 'Low', 'High', 'Critical'
            $table->enum('severity', ['Low', 'High', 'Critical'])->nullable()->after('category_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('queries', function (Blueprint $table) {
            // Dropping the foreign key constraint for 'category_id'
            $table->dropForeign(['category_id']);
            
            // Dropping the 'category_id' and 'severity' columns
            $table->dropColumn(['category_id', 'severity']);
        });
    }
};
