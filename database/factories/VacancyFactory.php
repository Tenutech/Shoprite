<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Position;
use App\Models\Store;
use App\Models\Type;
use App\Models\Vacancy;
use App\Models\VacancyStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

class VacancyFactory extends Factory
{
    protected $model = Vacancy::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => User::factory(), 
            'position_id' => function () {
                return \App\Models\Position::factory()->create()->id;
            },
            'store_id' => function () {
                return \App\Models\Store::factory()->create()->id;
            },
            'type_id' => function () {
                return \App\Models\Type::factory()->create()->id;
            },
            'status_id' => function () {
                return \App\Models\VacancyStatus::factory()->create()->id;
            },
            'open_positions' => $this->faker->numberBetween(1, 10),
            'filled_positions' => $this->faker->numberBetween(0, 10),
            'advertisement' => $this->faker->randomElement(['Any', 'Internal', 'External']),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}