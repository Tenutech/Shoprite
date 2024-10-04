<?php

namespace Database\Factories;

use App\Models\Town;
use App\Models\Province;
use Illuminate\Database\Eloquent\Factories\Factory;

class TownFactory extends Factory
{
    protected $model = Town::class;

    public function definition()
    {
        return [
            'name' => $this->faker->city,
            'code' => $this->faker->postcode,
            'province_id' => function () {
                return \App\Models\Province::factory()->create()->id;
            },
            'district' => $this->faker->word,
            'seat' => $this->faker->boolean,
            'class' => $this->faker->randomElement(['A', 'B', 'C'])
        ];
    }
}