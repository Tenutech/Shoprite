<?php

namespace Database\Seeders;

use App\Models\SubCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SubCategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SubCategory::create([
            'name' => 'Property', 
            'icon' => 'ri-community-line', 
            'lordicon' => 'https://cdn.lordicon.com/jjjmlddk.json'
        ]);

        SubCategory::create([
            'name' => 'Product Or Service', 
            'icon' => 'ri-hand-coin-line', 
            'lordicon' => 'https://cdn.lordicon.com/yyecuati.json'
        ]);

        SubCategory::create([
            'name' => 'Company', 
            'icon' => 'ri-funds-line', 
            'lordicon' => 'https://cdn.lordicon.com/cjieiyzp.json'
        ]);
    }
}
