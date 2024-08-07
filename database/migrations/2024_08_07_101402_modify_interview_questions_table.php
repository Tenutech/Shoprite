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
            // Drop existing columns
            $table->dropColumn(['color', 'icon']);

            // Add new columns with foreign key constraints
            $table->foreignId('template_id')->nullable()->constrained('interview_templates')->before('question')->onDelete('set null')->onUpdate('cascade');
            $table->enum('type', ['text', 'number', 'rating', 'textarea'])->after('question');
            $table->integer('sort')->default(0)->after('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('interview_questions', function (Blueprint $table) {
            // Add dropped columns back
            $table->string('color');
            $table->string('icon');

            // Drop new foreign key columns
            $table->dropForeign(['template_id']);
            $table->dropColumn(['template_id', 'question', 'type', 'sort']);
        });
    }
};
