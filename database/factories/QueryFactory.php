<?php

namespace Database\Factories;

use App\Models\Query;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class QueryFactory extends Factory
{
    protected $model = Query::class;

    public function definition()
    {
        return [
            'subject' => $this->faker->sentence,
            'body' => $this->faker->paragraph,
            'user_id' => User::factory(),
        ];
    }
}