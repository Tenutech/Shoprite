<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ChecksTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('checks')->insert([
            [
                'id' => 1,
                'name' => 'ID Verification',
                'icon' => 'ri-shield-user-line',
                'color' => 'primary',
                'updated_at' => Carbon::parse('2023-11-09 14:47:21'),
                'created_at' => Carbon::parse('2023-11-09 14:47:21'),
            ],
            [
                'id' => 2,
                'name' => 'Credit Check',
                'icon' => 'ri-secure-payment-line',
                'color' => 'secondary',
                'updated_at' => Carbon::parse('2023-11-09 14:47:21'),
                'created_at' => Carbon::parse('2023-11-09 14:47:21'),
            ],
            [
                'id' => 3,
                'name' => 'Fraud Check',
                'icon' => 'ri-git-repository-private-line',
                'color' => 'success',
                'updated_at' => Carbon::parse('2023-11-09 14:47:21'),
                'created_at' => Carbon::parse('2023-11-09 14:47:21'),
            ],
            [
                'id' => 4,
                'name' => 'Driver\'s License Check',
                'icon' => 'ri-car-line',
                'color' => 'info',
                'updated_at' => Carbon::parse('2023-11-09 14:47:21'),
                'created_at' => Carbon::parse('2023-11-09 14:47:21'),
            ],
            [
                'id' => 5,
                'name' => 'Bank Verification',
                'icon' => 'ri-bank-line',
                'color' => 'warning',
                'updated_at' => Carbon::parse('2023-11-09 14:47:21'),
                'created_at' => Carbon::parse('2023-11-09 14:47:21'),
            ],
        ]);
    }
}
