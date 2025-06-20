<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::disableQueryLog();

        $total = 5000;
        $batchSize = 100;

        // Disable event dispatching (optional but improves performance)
        User::unsetEventDispatcher();

        for ($i = 0; $i < $total; $i += $batchSize) {
            User::factory($batchSize)->create();
        }
    }
}
