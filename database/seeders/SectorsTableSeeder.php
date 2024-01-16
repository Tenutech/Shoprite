<?php

namespace Database\Seeders;

use App\Models\Sector;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SectorsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Sector::create(['name' => 'Agriculture', 'icon' => 'ri-seedling-line', 'color' => 'primary', 'image' => 'build/images/sector/agriculture.jpg']);
        Sector::create(['name' => 'Automotive', 'icon' => 'ri-car-line', 'color' => 'secondary', 'image' => 'build/images/sector/automotive.jpg']);
        Sector::create(['name' => 'Banking', 'icon' => 'ri-bank-line', 'color' => 'success', 'image' => 'build/images/sector/banking.jpg']);
        Sector::create(['name' => 'Construction', 'icon' => 'ri-hammer-line', 'color' => 'info', 'image' => 'build/images/sector/construction.jpg']);
        Sector::create(['name' => 'Consumer Goods', 'icon' => 'ri-shopping-cart-line', 'color' => 'warning', 'image' => 'build/images/sector/consumer-goods.jpg']);
        Sector::create(['name' => 'Education', 'icon' => 'ri-book-line', 'color' => 'danger', 'image' => 'build/images/sector/education.jpg']);
        Sector::create(['name' => 'Energy', 'icon' => 'ri-flashlight-line', 'color' => 'success', 'image' => 'build/images/sector/energy.jpg']);
        Sector::create(['name' => 'Finance', 'icon' => 'ri-coin-line', 'color' => 'primary', 'image' => 'build/images/sector/finance.jpg']);
        Sector::create(['name' => 'Healthcare', 'icon' => 'ri-hospital-line', 'color' => 'success', 'image' => 'build/images/sector/healthcare.jpg']);
        Sector::create(['name' => 'Hospitality', 'icon' => 'ri-hotel-line', 'color' => 'info', 'image' => 'build/images/sector/hospitality.jpg']);
        Sector::create(['name' => 'Information Technology', 'icon' => 'ri-computer-line', 'color' => 'dark', 'image' => 'build/images/sector/information-technology.jpg']);
        Sector::create(['name' => 'Insurance', 'icon' => 'ri-shield-line', 'color' => 'info', 'image' => 'build/images/sector/insurance.jpg']);
        Sector::create(['name' => 'Manufacturing', 'icon' => 'ri-building-3-line', 'color' => 'warning', 'image' => 'build/images/sector/manufacturing.jpg']);
        Sector::create(['name' => 'Media', 'icon' => 'ri-camera-line', 'color' => 'danger', 'image' => 'build/images/sector/media.jpg']);
        Sector::create(['name' => 'Mining', 'icon' => 'ri-tools-line', 'color' => 'dark', 'image' => 'build/images/sector/mining.jpg']);
        Sector::create(['name' => 'Pharmaceuticals', 'icon' => 'ri-medicine-bottle-line', 'color' => 'secondary', 'image' => 'build/images/sector/pharmaceuticals.jpg']);
        Sector::create(['name' => 'Real Estate', 'icon' => 'ri-building-4-line', 'color' => 'info', 'image' => 'build/images/sector/real-estate.jpg']);
        Sector::create(['name' => 'Retail', 'icon' => 'ri-store-line', 'color' => 'danger', 'image' => 'build/images/sector/retail.jpg']);
        Sector::create(['name' => 'Telecommunications', 'icon' => 'ri-signal-tower-line', 'color' => 'warning', 'image' => 'build/images/sector/telecommunications.jpg']);
        Sector::create(['name' => 'Transportation', 'icon' => 'ri-bus-line', 'color' => 'secondary', 'image' => 'build/images/sector/transportation.jpg']);
    }
}
