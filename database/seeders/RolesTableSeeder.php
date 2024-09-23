<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::create(['name' => 'Super Admin', 'icon' => 'ri-user-settings-fill', 'color' => 'primary']);
        Role::create(['name' => 'Admin', 'icon' => 'ri-admin-fill', 'color' => 'secondary']);
        Role::create(['name' => 'Regional People Partner', 'icon' => 'ri-admin-fill', 'color' => 'secondary']);
        Role::create(['name' => 'Divisional Talent Development Partner', 'icon' => 'ri-admin-fill', 'color' => 'secondary']);
        Role::create(['name' => 'Divisional People Partner', 'icon' => 'ri-admin-fill', 'color' => 'secondary']);
        Role::create(['name' => 'Manager', 'icon' => 'ri-user-add-line', 'color' => 'info']);
        Role::create(['name' => 'User', 'icon' => 'ri-user-location-fill', 'color' => 'success']);
        Role::create(['name' => 'Applicant', 'icon' => 'ri-profile-line', 'color' => 'warning']);
    }
}
