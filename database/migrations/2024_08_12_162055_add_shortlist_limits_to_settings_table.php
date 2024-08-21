<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddShortlistLimitsToSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('settings')->insert([
            [
                'key' => 'min_shorlist_number',
                'value' => '1',
                'description' => 'This setting determines the minimum number of candidates on a Shortlist.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'max_shorlist_number',
                'value' => '100',
                'description' => 'This setting determines the maximum number of candidates on a Shortlist.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('settings')->where('key', 'min_shorlist_number')->delete();
        DB::table('settings')->where('key', 'max_shorlist_number')->delete();
    }
}