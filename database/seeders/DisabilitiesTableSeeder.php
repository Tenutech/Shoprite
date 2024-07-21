<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DisabilitiesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('disabilities')->insert([
            [
                'id' => 1,
                'name' => 'Chronic Illness',
                'icon' => 'ri-wheelchair-line',
                'color' => 'warning',
                'created_at' => Carbon::parse('2023-09-14 14:42:03'),
                'updated_at' => Carbon::parse('2024-03-19 15:20:09'),
            ],
            [
                'id' => 2,
                'name' => 'Disease',
                'icon' => 'ri-wheelchair-line',
                'color' => 'info',
                'created_at' => Carbon::parse('2023-09-14 14:42:03'),
                'updated_at' => Carbon::parse('2023-09-14 14:42:03'),
            ],
            [
                'id' => 3,
                'name' => 'Disability',
                'icon' => 'ri-wheelchair-line',
                'color' => 'danger',
                'created_at' => Carbon::parse('2023-09-14 14:42:03'),
                'updated_at' => Carbon::parse('2023-09-14 14:42:03'),
            ],
            [
                'id' => 4,
                'name' => 'None',
                'icon' => 'ri-wheelchair-line',
                'color' => 'success',
                'created_at' => Carbon::parse('2023-09-14 14:42:03'),
                'updated_at' => Carbon::parse('2023-09-14 14:42:03'),
            ],
        ]);
    }
}
