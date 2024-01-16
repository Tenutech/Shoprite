<?php

namespace Database\Seeders;

use App\Models\Status;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StatusTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Status::create([
            'name' => 'Online', 
            'icon' => 'ri-checkbox-circle-fill',
            'color' => 'success', 
        ]);
        Status::create([
            'name' => 'Offline', 
            'icon' => 'ri-close-circle-fill',
            'color' => 'danger', 
        ]);
    }
}
