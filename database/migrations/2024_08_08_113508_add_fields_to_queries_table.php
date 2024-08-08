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
            $table->string('jira_issue_id', 191)->nullable()->after('id');
            $table->string('firstname', 191)->nullable()->after('user_id');
            $table->string('lastname', 191)->nullable()->after('firstname');
            $table->string('email', 191)->nullable()->after('lastname');
            $table->string('phone', 191)->nullable()->after('email');
            $table->enum('status', ['Pending', 'In Progress', 'Complete'])->default('Pending')->after('body');
            $table->text('answer')->nullable()->after('status');
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
            $table->dropColumn('jira_issue_id');
            $table->dropColumn('firstname');
            $table->dropColumn('lastname');
            $table->dropColumn('email');
            $table->dropColumn('phone');
            $table->dropColumn('status');
            $table->dropColumn('answer');
        });
    }
};
