<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GendersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('genders')->insert([
            [
                'id' => 1,
                'gender' => 'Male',
                'icon' => 'ri-men-line',
                'color' => 'primary',
                'created_at' => '2023-09-14 14:42:03',
                'updated_at' => '2024-03-19 15:24:39',
            ],
            [
                'id' => 2,
                'gender' => 'Female',
                'icon' => 'ri-women-line',
                'color' => 'secondary',
                'created_at' => '2023-09-14 14:42:03',
                'updated_at' => '2023-09-14 14:42:03',
            ],
        ]);
    }
}
