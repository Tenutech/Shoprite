<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('settings')->insert([
            [
                'id' => 1,
                'key' => 'vacancy_posting_duration',
                'value' => '30',
                'description' => 'This setting defines the default duration (in days) a job vacancy remains open before it automatically closes or requires manual extension.',
                'created_at' => '2024-03-05 11:37:45',
                'updated_at' => '2024-03-05 12:10:17',
            ],
            [
                'id' => 2,
                'key' => 'shortlist_expiry',
                'value' => '14',
                'description' => 'This setting specifies the time (in days) after which an applicant is removed from the shortlist if no interview has been scheduled.',
                'created_at' => '2024-03-05 11:37:45',
                'updated_at' => '2024-03-05 12:10:17',
            ],
            [
                'id' => 3,
                'key' => 'session_timeout',
                'value' => '30',
                'description' => 'This setting determines the duration (in minutes) after which a user\'s session is timed out due to inactivity, resulting in the user being logged out.',
                'created_at' => '2024-03-05 11:37:45',
                'updated_at' => '2024-03-05 12:15:48',
            ],
            [
                'id' => 4,
                'key' => 'store_search_radius',
                'value' => '15',
                'description' => 'This setting determines the default search radius when building a shortlist. The default is set in kilometers, for the moment 15km.',
                'created_at' => '2024-06-18 10:57:23',
                'updated_at' => '2024-06-18 10:59:01',
            ],
        ]);
    }
}
