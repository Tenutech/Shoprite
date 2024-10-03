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
        Schema::table('applicants', function (Blueprint $table) {
            // Add the employment enum column with default 'I' after the no_show column
            $table->enum('employment', ['A', 'B', 'I', 'N', 'P'])
                ->nullable()
                ->default('I')
                ->after('no_show');
        });
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('applicants', function (Blueprint $table) {
            // Remove the employment column
            $table->dropColumn('employment');
        });
    }
};
