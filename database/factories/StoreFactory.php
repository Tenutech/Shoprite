<?php

namespace Database\Factories;

use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

class StoreFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Store::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'brand_id' => function () {
                return \App\Models\Brand::factory()->create()->id;
            },
            'town_id' => function () {
                return \App\Models\Town::factory()->create()->id;
            },
            'address' => $this->faker->address,
            'coordinates' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}