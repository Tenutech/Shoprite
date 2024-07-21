<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReasonsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('reasons')->insert([
            [
                'id' => 1,
                'name' => 'Salary was not enough',
                'icon' => 'ri-briefcase-fill',
                'color' => 'danger',
                'created_at' => '2023-09-14 14:42:03',
                'updated_at' => '2023-09-14 14:42:03',
            ],
            [
                'id' => 2,
                'name' => 'I did not enjoy it',
                'icon' => 'ri-briefcase-fill',
                'color' => 'danger',
                'created_at' => '2023-09-14 14:42:03',
                'updated_at' => '2023-09-14 14:42:03',
            ],
            [
                'id' => 3,
                'name' => 'I moved away',
                'icon' => 'ri-briefcase-fill',
                'color' => 'danger',
                'created_at' => '2023-09-14 14:42:03',
                'updated_at' => '2023-09-14 14:42:03',
            ],
            [
                'id' => 4,
                'name' => 'I fell pregnant',
                'icon' => 'ri-briefcase-fill',
                'color' => 'danger',
                'created_at' => '2023-09-14 14:42:03',
                'updated_at' => '2023-09-14 14:42:03',
            ],
            [
                'id' => 5,
                'name' => 'I was dismissed',
                'icon' => 'ri-briefcase-fill',
                'color' => 'danger',
                'created_at' => '2023-09-14 14:42:03',
                'updated_at' => '2023-09-14 14:42:03',
            ],
            [
                'id' => 6,
                'name' => 'It was just a temporary/seasonal job',
                'icon' => 'ri-briefcase-fill',
                'color' => 'danger',
                'created_at' => '2023-09-14 14:42:03',
                'updated_at' => '2023-09-14 14:42:03',
            ],
            [
                'id' => 7,
                'name' => 'Got another job',
                'icon' => 'ri-briefcase-fill',
                'color' => 'danger',
                'created_at' => '2023-09-14 14:42:03',
                'updated_at' => '2023-09-14 14:42:03',
            ],
            [
                'id' => 8,
                'name' => 'Other',
                'icon' => 'ri-briefcase-fill',
                'color' => 'danger',
                'created_at' => '2023-09-14 14:42:03',
                'updated_at' => '2023-09-14 14:42:03',
            ],
        ]);
    }
}
