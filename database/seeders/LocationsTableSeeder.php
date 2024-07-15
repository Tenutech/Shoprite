<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LocationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Location::create(['name' => 'Eastern Cape']);
        Location::create(['name' => 'Free State']);
        Location::create(['name' => 'Gauteng']);
        Location::create(['name' => 'KwaZulu-Natal']);
        Location::create(['name' => 'Limpopo']);
        Location::create(['name' => 'Mpumalanga']);
        Location::create(['name' => 'Northern Cape']);
        Location::create(['name' => 'North West']);
        Location::create(['name' => 'Western Cape']);
    }
}
