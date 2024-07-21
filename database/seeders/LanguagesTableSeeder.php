<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LanguagesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('languages')->insert([
            [
                'id' => 1,
                'name' => 'Afrikaans',
                'icon' => 'ri-volume-up-line',
                'color' => 'primary',
                'created_at' => '2023-09-14 14:42:03',
                'updated_at' => '2023-09-14 14:42:03',
            ],
            [
                'id' => 2,
                'name' => 'English',
                'icon' => 'ri-volume-up-line',
                'color' => 'primary',
                'created_at' => '2023-09-14 14:42:03',
                'updated_at' => '2023-09-14 14:42:03',
            ],
            [
                'id' => 3,
                'name' => 'Ndebele',
                'icon' => 'ri-volume-up-line',
                'color' => 'primary',
                'created_at' => '2023-09-14 14:42:03',
                'updated_at' => '2023-09-14 14:42:03',
            ],
            [
                'id' => 4,
                'name' => 'Pedi',
                'icon' => 'ri-volume-up-line',
                'color' => 'primary',
                'created_at' => '2023-09-14 14:42:03',
                'updated_at' => '2023-09-14 14:42:03',
            ],
            [
                'id' => 5,
                'name' => 'Sotho',
                'icon' => 'ri-volume-up-line',
                'color' => 'primary',
                'created_at' => '2023-09-14 14:42:03',
                'updated_at' => '2023-09-14 14:42:03',
            ],
            [
                'id' => 6,
                'name' => 'Swati',
                'icon' => 'ri-volume-up-line',
                'color' => 'primary',
                'created_at' => '2023-09-14 14:42:03',
                'updated_at' => '2023-09-14 14:42:03',
            ],
            [
                'id' => 7,
                'name' => 'Tsonga',
                'icon' => 'ri-volume-up-line',
                'color' => 'primary',
                'created_at' => '2023-09-14 14:42:03',
                'updated_at' => '2023-09-14 14:42:03',
            ],
            [
                'id' => 8,
                'name' => 'Tswana',
                'icon' => 'ri-volume-up-line',
                'color' => 'primary',
                'created_at' => '2023-09-14 14:42:03',
                'updated_at' => '2023-09-14 14:42:03',
            ],
            [
                'id' => 9,
                'name' => 'Venda',
                'icon' => 'ri-volume-up-line',
                'color' => 'primary',
                'created_at' => '2023-09-14 14:42:03',
                'updated_at' => '2023-09-14 14:42:03',
            ],
            [
                'id' => 10,
                'name' => 'Xhosa',
                'icon' => 'ri-volume-up-line',
                'color' => 'primary',
                'created_at' => '2023-09-14 14:42:03',
                'updated_at' => '2023-09-14 14:42:03',
            ],
            [
                'id' => 11,
                'name' => 'Zulu',
                'icon' => 'ri-volume-up-line',
                'color' => 'primary',
                'created_at' => '2023-09-14 14:42:03',
                'updated_at' => '2023-09-14 14:42:03',
            ],
        ]);
    }
}
