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
        Schema::table('positions', function (Blueprint $table) {
            Schema::table('positions', function (Blueprint $table) {
                $table->foreignId('template_id')->nullable()->constrained('interview_templates')->after('image')->onDelete('set null')->onUpdate('cascade');
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('positions', function (Blueprint $table) {
            Schema::table('positions', function (Blueprint $table) {
                $table->dropForeign(['template_id']);
                $table->dropColumn(['template_id']);
            });
        });
    }
};
