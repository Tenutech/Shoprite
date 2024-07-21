<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ScoreWeightingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('score_weightings')->insert([
            [
                'id' => 1,
                'score_type' => 'literacy_score',
                'weight' => 30.00,
                'max_value' => 10.00,
                'condition_field' => null,
                'condition_value' => null,
                'fallback_value' => 0.00,
                'created_at' => '2024-02-12 14:30:10',
                'updated_at' => '2024-02-13 09:36:30',
            ],
            [
                'id' => 2,
                'score_type' => 'numeracy_score',
                'weight' => 30.00,
                'max_value' => 10.00,
                'condition_field' => null,
                'condition_value' => null,
                'fallback_value' => 0.00,
                'created_at' => '2024-02-12 14:30:10',
                'updated_at' => '2024-02-13 09:36:33',
            ],
            [
                'id' => 3,
                'score_type' => 'education_id',
                'weight' => 15.00,
                'max_value' => 6.00,
                'condition_field' => null,
                'condition_value' => null,
                'fallback_value' => 0.00,
                'created_at' => '2024-02-12 14:30:10',
                'updated_at' => '2024-02-13 09:36:35',
            ],
            [
                'id' => 4,
                'score_type' => 'duration_id',
                'weight' => 25.00,
                'max_value' => 6.00,
                'condition_field' => null,
                'condition_value' => null,
                'fallback_value' => 0.00,
                'created_at' => '2024-02-12 14:30:10',
                'updated_at' => '2024-02-13 09:36:37',
            ],
        ]);
    }
}
