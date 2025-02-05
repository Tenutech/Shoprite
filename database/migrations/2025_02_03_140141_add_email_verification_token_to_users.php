<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        if (!Schema::hasColumn('users', 'email_verification_token') ||
            !Schema::hasColumn('users', 'email_verification_expires_at')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'email_verification_token')) {
                    $table->string('email_verification_token')->nullable()->after('email_verified_at');
                }

                if (!Schema::hasColumn('users', 'email_verification_expires_at')) {
                    $table->timestamp('email_verification_expires_at')->nullable()->after('email_verification_token');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'email_verification_token')) {
                $table->dropColumn('email_verification_token');
            }

            if (Schema::hasColumn('users', 'email_verification_expires_at')) {
                $table->dropColumn('email_verification_expires_at');
            }
        });
    }
};
