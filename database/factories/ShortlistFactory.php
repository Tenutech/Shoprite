<?php

namespace Database\Factories;

use App\Models\Shortlist;
use Illuminate\Database\Eloquent\Factories\Factory;

class ShortlistFactory extends Factory
{
    protected $model = Shortlist::class;

    public function definition()
    {
        return [
            'user_id' => 1,
            'vacancy_id' => function () {
                return \App\Models\Vacancy::factory()->create()->id;
            },
            'created_at' => $this->faker->dateTimeBetween('-10 days', 'now'),
        ];
    }
}