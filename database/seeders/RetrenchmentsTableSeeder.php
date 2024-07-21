<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RetrenchmentsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('retrenchments')->insert([
            [
                'id' => 1,
                'name' => 'Dismissed',
                'icon' => 'ri-briefcase-3-fill',
                'color' => 'danger',
                'created_at' => '2023-09-14 14:42:03',
                'updated_at' => '2023-09-14 14:42:03',
            ],
            [
                'id' => 2,
                'name' => 'Retrenched',
                'icon' => 'ri-briefcase-3-fill',
                'color' => 'warning',
                'created_at' => '2023-09-14 14:42:03',
                'updated_at' => '2023-09-14 14:42:03',
            ],
            [
                'id' => 3,
                'name' => 'Never',
                'icon' => 'ri-briefcase-3-fill',
                'color' => 'success',
                'created_at' => '2023-09-14 14:42:03',
                'updated_at' => '2023-09-14 14:42:03',
            ],
        ]);
    }
}
