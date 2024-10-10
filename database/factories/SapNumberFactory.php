<?php

namespace Database\Factories;

use App\Models\SapNumber;
use Illuminate\Database\Eloquent\Factories\Factory;

class SapNumberFactory extends Factory
{
    protected $model = Role::class;

    public function definition()
    {
        return [
            'description' => $this->faker->word,
            'sap_number' => $this->faker->numberBetween(10000000, 99999999),
            'vacancy_id' => function () {
                return \App\Models\Vacancy::factory()->create()->id;
            },
        ];
    }
}