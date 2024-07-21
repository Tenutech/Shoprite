<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('interview_questions', function (Blueprint $table) {
            $table->unsignedBigInteger('question_template_id')->nullable();

            $table->foreign('question_template_id')->references('id')->on('interview_question_templates')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('interview_questions', function (Blueprint $table) {
            $table->dropForeign(['question_template_id']);
            
            $table->dropColumn('question_template_id');
        });
    }
};
