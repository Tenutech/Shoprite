<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RacesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('races')->insert([
            [
                'id' => 1,
                'name' => 'African',
                'icon' => 'ri-user-line',
                'color' => 'success',
                'created_at' => '2023-09-14 14:42:03',
                'updated_at' => '2024-03-19 15:27:31',
            ],
            [
                'id' => 2,
                'name' => 'Asian',
                'icon' => 'ri-user-line',
                'color' => 'warning',
                'created_at' => '2023-09-14 14:42:03',
                'updated_at' => '2023-09-14 14:42:03',
            ],
            [
                'id' => 3,
                'name' => 'Coloured',
                'icon' => 'ri-user-line',
                'color' => 'danger',
                'created_at' => '2023-09-14 14:42:03',
                'updated_at' => '2023-09-14 14:42:03',
            ],
            [
                'id' => 4,
                'name' => 'Indian',
                'icon' => 'ri-user-line',
                'color' => 'secondary',
                'created_at' => '2023-09-14 14:42:03',
                'updated_at' => '2023-09-14 14:42:03',
            ],
            [
                'id' => 5,
                'name' => 'White',
                'icon' => 'ri-user-line',
                'color' => 'primary',
                'created_at' => '2023-09-14 14:42:03',
                'updated_at' => '2023-09-14 14:42:03',
            ],
        ]);
    }
}
