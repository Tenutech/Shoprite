<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DurationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('durations')->insert([
            [
                'id' => 1,
                'name' => 'One month or less',
                'icon' => 'ri-time-line',
                'color' => 'primary',
                'created_at' => Carbon::parse('2023-09-14 14:42:03'),
                'updated_at' => Carbon::parse('2023-09-14 14:42:03'),
            ],
            [
                'id' => 2,
                'name' => 'Two to six months',
                'icon' => 'ri-time-line',
                'color' => 'primary',
                'created_at' => Carbon::parse('2023-09-14 14:42:03'),
                'updated_at' => Carbon::parse('2023-09-14 14:42:03'),
            ],
            [
                'id' => 3,
                'name' => 'Seven months to a year',
                'icon' => 'ri-time-line',
                'color' => 'primary',
                'created_at' => Carbon::parse('2023-09-14 14:42:03'),
                'updated_at' => Carbon::parse('2023-09-14 14:42:03'),
            ],
            [
                'id' => 4,
                'name' => 'One to two years',
                'icon' => 'ri-time-line',
                'color' => 'primary',
                'created_at' => Carbon::parse('2023-09-14 14:42:03'),
                'updated_at' => Carbon::parse('2023-09-14 14:42:03'),
            ],
            [
                'id' => 5,
                'name' => 'Two to five years',
                'icon' => 'ri-time-line',
                'color' => 'primary',
                'created_at' => Carbon::parse('2023-09-14 14:42:03'),
                'updated_at' => Carbon::parse('2023-09-14 14:42:03'),
            ],
            [
                'id' => 6,
                'name' => 'More than five years',
                'icon' => 'ri-time-line',
                'color' => 'primary',
                'created_at' => Carbon::parse('2023-09-14 14:42:03'),
                'updated_at' => Carbon::parse('2023-09-14 14:42:03'),
            ],
        ]);
    }
}
