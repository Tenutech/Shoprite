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
            // Adding the situational fields
            $table->string('situational_question_pool', 191)->nullable()->after('numeracy');
            $table->integer('situational_score')->nullable()->after('situational_question_pool');
            $table->integer('situational_questions')->nullable()->after('situational_score');
            $table->string('situational', 10)->nullable()->after('situational_questions');
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
            // Dropping the situational fields
            $table->dropColumn('situational_question_pool');
            $table->dropColumn('situational_score');
            $table->dropColumn('situational_questions');
            $table->dropColumn('situational');
        });
    }
};
