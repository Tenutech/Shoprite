<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EducationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('educations')->insert([
            [
                'id' => 1,
                'name' => 'Grade 9',
                'icon' => 'ri-book-line',
                'color' => 'danger',
                'created_at' => Carbon::parse('2023-09-14 14:42:03'),
                'updated_at' => Carbon::parse('2023-09-14 14:42:03'),
            ],
            [
                'id' => 2,
                'name' => 'Grade 10',
                'icon' => 'ri-book-line',
                'color' => 'warning',
                'created_at' => Carbon::parse('2023-09-14 14:42:03'),
                'updated_at' => Carbon::parse('2023-09-14 14:42:03'),
            ],
            [
                'id' => 3,
                'name' => 'Grade 11',
                'icon' => 'ri-book-line',
                'color' => 'info',
                'created_at' => Carbon::parse('2023-09-14 14:42:03'),
                'updated_at' => Carbon::parse('2023-09-14 14:42:03'),
            ],
            [
                'id' => 4,
                'name' => 'Grade 12',
                'icon' => 'ri-book-line',
                'color' => 'success',
                'created_at' => Carbon::parse('2023-09-14 14:42:03'),
                'updated_at' => Carbon::parse('2023-09-14 14:42:03'),
            ],
            [
                'id' => 5,
                'name' => 'College/Technicon',
                'icon' => 'ri-book-line',
                'color' => 'secondary',
                'created_at' => Carbon::parse('2023-09-14 14:42:03'),
                'updated_at' => Carbon::parse('2023-09-14 14:42:03'),
            ],
            [
                'id' => 6,
                'name' => 'University',
                'icon' => 'ri-book-line',
                'color' => 'primary',
                'created_at' => Carbon::parse('2023-09-14 14:42:03'),
                'updated_at' => Carbon::parse('2023-09-14 14:42:03'),
            ],
        ]);
    }
}
