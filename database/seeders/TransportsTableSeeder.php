<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TransportsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('transports')->insert([
            ['id' => 1, 'name' => 'Bicycle', 'icon' => 'ri-bike-fill', 'color' => 'secondary', 'created_at' => '2023-10-10 09:39:03', 'updated_at' => '2023-10-10 09:39:03'],
            ['id' => 2, 'name' => 'Bus', 'icon' => 'ri-bus-fill', 'color' => 'info', 'created_at' => '2023-10-10 09:39:03', 'updated_at' => '2023-10-10 09:39:03'],
            ['id' => 3, 'name' => 'Hitchhike', 'icon' => 'ri-thumb-up-fill', 'color' => 'danger', 'created_at' => '2023-10-10 09:39:03', 'updated_at' => '2023-10-10 09:39:03'],
            ['id' => 4, 'name' => 'Lift Club', 'icon' => 'ri-group-fill', 'color' => 'primary', 'created_at' => '2023-10-10 09:39:03', 'updated_at' => '2023-10-10 09:39:03'],
            ['id' => 5, 'name' => 'Own Car', 'icon' => 'ri-car-fill', 'color' => 'primary', 'created_at' => '2023-10-10 09:39:03', 'updated_at' => '2023-10-10 09:39:03'],
            ['id' => 6, 'name' => 'Taxi', 'icon' => 'ri-taxi-fill', 'color' => 'warning', 'created_at' => '2023-10-10 09:39:03', 'updated_at' => '2023-10-10 09:39:03'],
            ['id' => 7, 'name' => 'Train', 'icon' => 'ri-train-fill', 'color' => 'info', 'created_at' => '2023-10-10 09:39:03', 'updated_at' => '2023-10-10 09:39:03'],
            ['id' => 8, 'name' => 'Walk', 'icon' => 'ri-walk-fill', 'color' => 'danger', 'created_at' => '2023-10-10 09:39:03', 'updated_at' => '2023-10-10 09:39:03'],
            ['id' => 9, 'name' => 'Other', 'icon' => 'ri-arrow-left-right-fill', 'color' => 'dark', 'created_at' => '2023-10-10 09:39:03', 'updated_at' => '2023-10-10 09:39:03'],
        ]);
    }
}
