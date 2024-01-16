<?php

namespace Database\Seeders;

use App\Models\Type;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TypeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Type::create(['name' => 'Consultant', 'icon' => 'ri-speak-line']);
        Type::create(['name' => 'Financial Services', 'icon' => 'ri-funds-line']);
        Type::create(['name' => 'Manufacturing', 'icon' => 'ri-building-3-line']);
        Type::create(['name' => 'Mechanical Services', 'icon' => 'ri-settings-5-line']);
        Type::create(['name' => 'Other Technical Services', 'icon' => 'ri-shake-hands-line']);
    }
}
