<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BanksTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('banks')->insert([
            [
                'id' => 1,
                'name' => 'Absa Bank',
                'icon' => 'ri-bank-line',
                'color' => 'danger',
                'created_at' => Carbon::parse('2023-09-14 14:42:03'),
                'updated_at' => Carbon::parse('2024-03-19 15:06:07'),
            ],
            [
                'id' => 2,
                'name' => 'African Bank',
                'icon' => 'ri-bank-line',
                'color' => 'primary',
                'created_at' => Carbon::parse('2023-09-14 14:42:03'),
                'updated_at' => Carbon::parse('2023-09-14 14:42:03'),
            ],
            [
                'id' => 3,
                'name' => 'Bidvest Bank',
                'icon' => 'ri-bank-line',
                'color' => 'warning',
                'created_at' => Carbon::parse('2023-09-14 14:42:03'),
                'updated_at' => Carbon::parse('2023-09-14 14:42:03'),
            ],
            [
                'id' => 4,
                'name' => 'Capitec Bank',
                'icon' => 'ri-bank-line',
                'color' => 'danger',
                'created_at' => Carbon::parse('2023-09-14 14:42:03'),
                'updated_at' => Carbon::parse('2023-09-14 14:42:03'),
            ],
            [
                'id' => 5,
                'name' => 'Discovery Bank',
                'icon' => 'ri-bank-line',
                'color' => 'primary',
                'created_at' => Carbon::parse('2023-09-14 14:42:03'),
                'updated_at' => Carbon::parse('2023-09-14 14:42:03'),
            ],
            [
                'id' => 6,
                'name' => 'First National Bank',
                'icon' => 'ri-bank-line',
                'color' => 'success',
                'created_at' => Carbon::parse('2023-09-14 14:42:03'),
                'updated_at' => Carbon::parse('2023-09-14 14:42:03'),
            ],
            [
                'id' => 7,
                'name' => 'Nedbank',
                'icon' => 'ri-bank-line',
                'color' => 'success',
                'created_at' => Carbon::parse('2023-09-14 14:42:03'),
                'updated_at' => Carbon::parse('2023-09-14 14:42:03'),
            ],
            [
                'id' => 8,
                'name' => 'Standard Bank',
                'icon' => 'ri-bank-line',
                'color' => 'info',
                'created_at' => Carbon::parse('2023-09-14 14:42:03'),
                'updated_at' => Carbon::parse('2023-09-14 14:42:03'),
            ],
            [
                'id' => 9,
                'name' => 'Other',
                'icon' => 'ri-bank-line',
                'color' => 'light',
                'created_at' => Carbon::parse('2023-09-14 14:42:03'),
                'updated_at' => Carbon::parse('2023-09-14 14:42:03'),
            ],
        ]);
    }
}
