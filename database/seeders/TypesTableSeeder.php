<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('types')->insert([
            ['id' => 1, 'name' => 'Full Time', 'icon' => 'ri-briefcase-2-line', 'lordicon' => 'https://cdn.lordicon.com/xzalkbkz.json', 'color' => 'success', 'created_at' => '2023-10-05 11:10:24', 'updated_at' => '2023-10-05 11:10:24'],
            ['id' => 2, 'name' => 'Seasonal', 'icon' => 'ri-briefcase-2-line', 'lordicon' => 'https://cdn.lordicon.com/qvyppzqz.json', 'color' => 'info', 'created_at' => '2023-10-05 11:10:24', 'updated_at' => '2023-10-05 11:10:24'],
            ['id' => 3, 'name' => 'Formal Internship', 'icon' => 'ri-briefcase-2-line', 'lordicon' => 'https://cdn.lordicon.com/uecgmesg.json', 'color' => 'secondary', 'created_at' => '2023-10-05 11:10:24', 'updated_at' => '2023-10-05 11:10:24'],
            ['id' => 4, 'name' => 'Learnership', 'icon' => 'ri-briefcase-2-line', 'lordicon' => 'https://cdn.lordicon.com/jjoolpwc.json', 'color' => 'warning', 'created_at' => '2023-10-05 11:10:24', 'updated_at' => '2023-10-05 11:10:24'],
            ['id' => 5, 'name' => 'Co-Operative Training', 'icon' => 'ri-briefcase-2-line', 'lordicon' => 'https://cdn.lordicon.com/pbbsmkso.json', 'color' => 'primary', 'created_at' => '2023-10-05 11:10:24', 'updated_at' => '2023-10-05 11:10:24'],
            ['id' => 6, 'name' => 'Other', 'icon' => 'ri-briefcase-2-line', 'lordicon' => NULL, 'color' => 'danger', 'created_at' => '2023-10-05 11:10:24', 'updated_at' => '2023-10-05 11:10:24'],
        ]);
    }
}
