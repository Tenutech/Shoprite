<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('roles')->insert([
            [
                'id' => 1,
                'name' => 'Super Admin',
                'icon' => 'ri-user-settings-fill',
                'color' => 'primary',
                'created_at' => '2023-09-05 13:19:28',
                'updated_at' => '2023-09-05 13:19:28',
            ],
            [
                'id' => 2,
                'name' => 'Admin',
                'icon' => 'ri-admin-fill',
                'color' => 'secondary',
                'created_at' => '2023-09-05 13:19:28',
                'updated_at' => '2023-09-05 13:19:28',
            ],
            [
                'id' => 3,
                'name' => 'Manager',
                'icon' => 'ri-user-add-fill',
                'color' => 'info',
                'created_at' => '2023-09-05 13:19:28',
                'updated_at' => '2023-09-05 13:19:28',
            ],
            [
                'id' => 4,
                'name' => 'User',
                'icon' => 'ri-user-location-fill',
                'color' => 'success',
                'created_at' => '2023-09-05 13:19:28',
                'updated_at' => '2023-09-05 13:19:28',
            ],
            [
                'id' => 5,
                'name' => 'Applicant',
                'icon' => 'ri-profile-line',
                'color' => 'warning',
                'created_at' => '2023-09-05 13:19:28',
                'updated_at' => '2023-09-05 13:19:28',
            ],
        ]);
    }
}
