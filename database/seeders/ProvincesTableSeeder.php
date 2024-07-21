<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProvincesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('provinces')->insert([
            [
                'id' => 1,
                'name' => 'Eastern Cape',
                'created_at' => '2023-05-11 14:03:00',
                'updated_at' => '2024-03-19 14:53:36',
            ],
            [
                'id' => 2,
                'name' => 'Free State',
                'created_at' => '2023-05-11 14:03:00',
                'updated_at' => '2023-05-11 14:03:00',
            ],
            [
                'id' => 3,
                'name' => 'Gauteng',
                'created_at' => '2023-05-11 14:03:00',
                'updated_at' => '2023-05-11 14:03:00',
            ],
            [
                'id' => 4,
                'name' => 'KwaZulu-Natal',
                'created_at' => '2023-05-11 14:03:00',
                'updated_at' => '2023-05-11 14:03:00',
            ],
            [
                'id' => 5,
                'name' => 'Limpopo',
                'created_at' => '2023-05-11 14:03:00',
                'updated_at' => '2023-05-11 14:03:00',
            ],
            [
                'id' => 6,
                'name' => 'Mpumalanga',
                'created_at' => '2023-05-11 14:03:00',
                'updated_at' => '2023-05-11 14:03:00',
            ],
            [
                'id' => 7,
                'name' => 'Northern Cape',
                'created_at' => '2023-05-11 14:03:00',
                'updated_at' => '2023-05-11 14:03:00',
            ],
            [
                'id' => 8,
                'name' => 'North West',
                'created_at' => '2023-05-11 14:03:00',
                'updated_at' => '2023-05-11 14:03:00',
            ],
            [
                'id' => 9,
                'name' => 'Western Cape',
                'created_at' => '2023-05-11 14:03:00',
                'updated_at' => '2023-05-11 14:03:00',
            ],
        ]);
    }
}
