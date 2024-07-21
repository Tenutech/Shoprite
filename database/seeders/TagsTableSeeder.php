<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TagsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('tags')->insert([
            ['id' => 1, 'name' => 'Finance', 'icon' => 'ri-briefcase-line', 'color' => 'primary', 'created_at' => '2023-10-27 12:24:39', 'updated_at' => '2023-10-27 12:24:39'],
            ['id' => 2, 'name' => 'Baking', 'icon' => 'ri-cake-3-line', 'color' => 'secondary', 'created_at' => '2023-10-27 12:24:39', 'updated_at' => '2023-10-27 12:24:39'],
            ['id' => 3, 'name' => 'Meat Processing', 'icon' => 'ri-knife-line', 'color' => 'danger', 'created_at' => '2023-10-27 12:24:39', 'updated_at' => '2023-10-27 12:24:39'],
            ['id' => 4, 'name' => 'Customer Service', 'icon' => 'ri-customer-service-line', 'color' => 'warning', 'created_at' => '2023-10-27 12:24:39', 'updated_at' => '2023-10-27 12:24:39'],
            ['id' => 5, 'name' => 'Administration', 'icon' => 'ri-file-line', 'color' => 'success', 'created_at' => '2023-10-27 12:24:39', 'updated_at' => '2023-10-27 12:24:39'],
            ['id' => 6, 'name' => 'Food Service', 'icon' => 'ri-knife-line', 'color' => 'danger', 'created_at' => '2023-10-27 12:24:39', 'updated_at' => '2023-10-27 12:24:39'],
            ['id' => 7, 'name' => 'General Labor', 'icon' => 'ri-hammer-line', 'color' => 'dark', 'created_at' => '2023-10-27 12:24:39', 'updated_at' => '2023-10-27 12:24:39'],
            ['id' => 8, 'name' => 'Stock Management', 'icon' => 'ri-archive-line', 'color' => 'primary', 'created_at' => '2023-10-27 12:24:39', 'updated_at' => '2023-10-27 12:24:39'],
            ['id' => 9, 'name' => 'Sales', 'icon' => 'ri-price-tag-line', 'color' => 'secondary', 'created_at' => '2023-10-27 12:24:39', 'updated_at' => '2023-10-27 12:24:39'],
            ['id' => 10, 'name' => 'Management', 'icon' => 'ri-team-line', 'color' => 'info', 'created_at' => '2023-10-27 12:24:39', 'updated_at' => '2023-10-27 12:24:39'],
            ['id' => 11, 'name' => 'Logistics', 'icon' => 'ri-truck-line', 'color' => 'warning', 'created_at' => '2023-10-27 12:24:39', 'updated_at' => '2023-10-27 12:24:39'],
            ['id' => 12, 'name' => 'Quality Control', 'icon' => 'ri-check-line', 'color' => 'secondary', 'created_at' => '2023-10-27 12:24:39', 'updated_at' => '2023-10-27 12:24:39'],
            ['id' => 13, 'name' => 'Marketing', 'icon' => 'ri-ad-line', 'color' => 'success', 'created_at' => '2023-10-27 12:24:39', 'updated_at' => '2023-10-27 12:24:39'],
            ['id' => 14, 'name' => 'Human Resources', 'icon' => 'ri-user-line', 'color' => 'dark', 'created_at' => '2023-10-27 12:24:39', 'updated_at' => '2023-10-27 12:24:39'],
            ['id' => 15, 'name' => 'Training', 'icon' => 'ri-book-line', 'color' => 'primary', 'created_at' => '2023-10-27 12:24:39', 'updated_at' => '2023-10-27 12:24:39'],
            ['id' => 16, 'name' => 'Security', 'icon' => 'ri-shield-line', 'color' => 'secondary', 'created_at' => '2023-10-27 12:24:39', 'updated_at' => '2023-10-27 12:24:39'],
            ['id' => 17, 'name' => 'Health & Safety', 'icon' => 'ri-first-aid-kit-line', 'color' => 'info', 'created_at' => '2023-10-27 12:24:39', 'updated_at' => '2023-10-27 12:24:39'],
            ['id' => 18, 'name' => 'IT & Tech', 'icon' => 'ri-computer-line', 'color' => 'warning', 'created_at' => '2023-10-27 12:24:39', 'updated_at' => '2023-10-27 12:24:39'],
            ['id' => 19, 'name' => 'Procurement', 'icon' => 'ri-shopping-cart-line', 'color' => 'danger', 'created_at' => '2023-10-27 12:24:39', 'updated_at' => '2023-10-27 12:24:39'],
            ['id' => 20, 'name' => 'Facility Management', 'icon' => 'ri-building-line', 'color' => 'success', 'created_at' => '2023-10-27 12:24:39', 'updated_at' => '2023-10-27 12:24:39'],
        ]);
    }
}
