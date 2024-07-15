<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Category::create([
            'name' => 'I Want To Buy', 
            'icon' => 'ri-wallet-3-line', 
            'lordicon' => 'https://cdn.lordicon.com/dnoiydox.json'
        ]);

        Category::create([
            'name' => 'I Want To Sell', 
            'icon' => 'ri-store-3-line', 
            'lordicon' => 'https://cdn.lordicon.com/uqpazftn.json'
        ]);

        Category::create([
            'name' => 'I Want To Invest', 
            'icon' => 'ri-building-line', 
            'lordicon' => 'https://cdn.lordicon.com/qhviklyi.json'
        ]);

        Category::create([
            'name' => 'I Want To Raise Money', 
            'icon' => 'ri-money-dollar-circle-line', 
            'lordicon' => 'https://cdn.lordicon.com/vaeagfzc.json'
        ]);
    }
}
