<?php

namespace Database\Seeders;

use App\Models\Value;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ValuesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Value::create(['name' => 'R0 - R10m']);
        Value::create(['name' => 'R10 - R50m']);
        Value::create(['name' => 'R50 - R100m']);
        Value::create(['name' => 'R100m +']);
    }
}
