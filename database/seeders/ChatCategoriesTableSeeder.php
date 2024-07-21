<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ChatCategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('chat_categories')->insert([
            [
                'id' => 1,
                'name' => 'welcome',
                'created_at' => Carbon::parse('2023-10-05 17:01:43'),
                'updated_at' => Carbon::parse('2023-10-05 17:01:43'),
            ],
            [
                'id' => 2,
                'name' => 'info',
                'created_at' => Carbon::parse('2023-10-05 17:01:43'),
                'updated_at' => Carbon::parse('2023-10-05 17:01:43'),
            ],
            [
                'id' => 3,
                'name' => 'qualification',
                'created_at' => Carbon::parse('2023-10-05 17:01:43'),
                'updated_at' => Carbon::parse('2023-10-05 17:01:43'),
            ],
            [
                'id' => 4,
                'name' => 'experience',
                'created_at' => Carbon::parse('2023-10-05 17:01:43'),
                'updated_at' => Carbon::parse('2023-10-05 17:01:43'),
            ],
            [
                'id' => 5,
                'name' => 'punctuality',
                'created_at' => Carbon::parse('2023-10-05 17:01:43'),
                'updated_at' => Carbon::parse('2023-10-05 17:01:43'),
            ],
            [
                'id' => 6,
                'name' => 'reason',
                'created_at' => Carbon::parse('2023-10-05 17:01:43'),
                'updated_at' => Carbon::parse('2023-10-05 17:01:43'),
            ],
            [
                'id' => 7,
                'name' => 'literacy_start',
                'created_at' => Carbon::parse('2023-10-05 17:01:43'),
                'updated_at' => Carbon::parse('2023-10-05 17:01:43'),
            ],
            [
                'id' => 8,
                'name' => 'literacy',
                'created_at' => Carbon::parse('2023-10-05 17:01:43'),
                'updated_at' => Carbon::parse('2023-10-05 17:01:43'),
            ],
            [
                'id' => 9,
                'name' => 'numeracy_start',
                'created_at' => Carbon::parse('2023-10-05 17:01:43'),
                'updated_at' => Carbon::parse('2023-10-05 17:01:43'),
            ],
            [
                'id' => 10,
                'name' => 'numeracy',
                'created_at' => Carbon::parse('2023-10-05 17:01:43'),
                'updated_at' => Carbon::parse('2023-10-05 17:01:43'),
            ],
            [
                'id' => 11,
                'name' => 'complete',
                'created_at' => Carbon::parse('2023-10-05 17:01:43'),
                'updated_at' => Carbon::parse('2023-10-05 17:01:43'),
            ],
            [
                'id' => 12,
                'name' => 'checkpoint',
                'created_at' => Carbon::parse('2023-10-05 17:01:43'),
                'updated_at' => Carbon::parse('2023-10-05 17:01:43'),
            ],
            [
                'id' => 13,
                'name' => 'schedule',
                'created_at' => Carbon::parse('2023-10-05 17:01:43'),
                'updated_at' => Carbon::parse('2023-10-05 17:01:43'),
            ],
        ]);
    }
}
