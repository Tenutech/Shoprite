<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::create(['name' => 'Super Admin', 'icon' => 'ri-user-settings-fill']);
        Role::create(['name' => 'Admin', 'icon' => 'ri-admin-fill']);
        Role::create(['name' => 'Seller', 'icon' => 'ri-user-add-line']);
        Role::create(['name' => 'Buyer', 'icon' => 'ri-user-location-fill']);
    }
}
