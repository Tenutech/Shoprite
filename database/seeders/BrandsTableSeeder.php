<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BrandsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('brands')->insert([
            [
                'id' => 1,
                'name' => 'Checkers',
                'icon' => 'build/images/brands/checkers-logo.svg',
                'color' => 'info',
                'created_at' => Carbon::parse('2023-10-10 09:19:11'),
                'updated_at' => Carbon::parse('2023-10-10 09:19:11'),
            ],
            [
                'id' => 2,
                'name' => 'CheckersFoods',
                'icon' => 'build/images/brands/checkers-foods-logo.svg',
                'color' => 'info',
                'created_at' => Carbon::parse('2023-10-10 09:19:11'),
                'updated_at' => Carbon::parse('2023-10-10 09:19:11'),
            ],
            [
                'id' => 3,
                'name' => 'CheckersHyper',
                'icon' => 'build/images/brands/checkershyper-logo.svg',
                'color' => 'info',
                'created_at' => Carbon::parse('2023-10-10 09:19:11'),
                'updated_at' => Carbon::parse('2023-10-10 09:19:11'),
            ],
            [
                'id' => 4,
                'name' => 'CheckersSixty60',
                'icon' => 'build/images/brands/checkers-sixty60.svg',
                'color' => 'info',
                'created_at' => Carbon::parse('2023-10-10 09:19:11'),
                'updated_at' => Carbon::parse('2023-10-10 09:19:11'),
            ],
            [
                'id' => 5,
                'name' => 'House&Home',
                'icon' => 'build/images/brands/househome-logo.svg',
                'color' => 'success',
                'created_at' => Carbon::parse('2023-10-10 09:19:11'),
                'updated_at' => Carbon::parse('2023-10-10 09:19:11'),
            ],
            [
                'id' => 6,
                'name' => 'knect',
                'icon' => 'build/images/brands/knect-logo.svg',
                'color' => 'danger',
                'created_at' => Carbon::parse('2023-10-10 09:19:11'),
                'updated_at' => Carbon::parse('2023-10-10 09:19:11'),
            ],
            [
                'id' => 7,
                'name' => 'LiquorShop',
                'icon' => 'build/images/brands/shoprite-liquorshop-logo.svg',
                'color' => 'danger',
                'created_at' => Carbon::parse('2023-10-10 09:19:11'),
                'updated_at' => Carbon::parse('2023-10-10 09:19:11'),
            ],
            [
                'id' => 8,
                'name' => 'littleme',
                'icon' => 'build/images/brands/littleme-logo.svg',
                'color' => 'secondary',
                'created_at' => Carbon::parse('2023-10-10 09:19:11'),
                'updated_at' => Carbon::parse('2023-10-10 09:19:11'),
            ],
            [
                'id' => 9,
                'name' => 'Medirite',
                'icon' => 'build/images/brands/medirite-logo.svg',
                'color' => 'success',
                'created_at' => Carbon::parse('2023-10-10 09:19:11'),
                'updated_at' => Carbon::parse('2023-10-10 09:19:11'),
            ],
            [
                'id' => 10,
                'name' => 'OK Franchise',
                'icon' => 'build/images/brands/ok-franchise-logo.svg',
                'color' => 'danger',
                'created_at' => Carbon::parse('2023-10-10 09:19:11'),
                'updated_at' => Carbon::parse('2023-10-10 09:19:11'),
            ],
            [
                'id' => 11,
                'name' => 'OKFURNITURE',
                'icon' => 'build/images/brands/ok-furniture-logo.svg',
                'color' => 'danger',
                'created_at' => Carbon::parse('2023-10-10 09:19:11'),
                'updated_at' => Carbon::parse('2023-10-10 09:19:11'),
            ],
            [
                'id' => 12,
                'name' => 'Outdoor',
                'icon' => 'build/images/brands/checkers-outdoor-logo.svg',
                'color' => 'primary',
                'created_at' => Carbon::parse('2023-10-10 09:19:11'),
                'updated_at' => Carbon::parse('2023-10-10 09:19:11'),
            ],
            [
                'id' => 13,
                'name' => 'Petshop',
                'icon' => 'build/images/brands/petshop-logo.svg',
                'color' => 'dark',
                'created_at' => Carbon::parse('2023-10-10 09:19:11'),
                'updated_at' => Carbon::parse('2023-10-10 09:19:11'),
            ],
            [
                'id' => 14,
                'name' => 'Shoprite',
                'icon' => 'build/images/brands/shoprite-logo.svg',
                'color' => 'danger',
                'created_at' => Carbon::parse('2023-10-10 09:19:11'),
                'updated_at' => Carbon::parse('2023-10-10 09:19:11'),
            ],
            [
                'id' => 15,
                'name' => 'UNIQ',
                'icon' => 'build/images/brands/uniq-logo.svg',
                'color' => 'dark',
                'created_at' => Carbon::parse('2023-10-10 09:19:11'),
                'updated_at' => Carbon::parse('2023-10-10 09:19:11'),
            ],
            [
                'id' => 16,
                'name' => 'USave',
                'icon' => 'build/images/brands/usave-logo.svg',
                'color' => 'warning',
                'created_at' => Carbon::parse('2023-10-10 09:19:11'),
                'updated_at' => Carbon::parse('2023-10-10 09:19:11'),
            ],
        ]);
    }
}
