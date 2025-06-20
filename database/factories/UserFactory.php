<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'firstname' => $this->faker->firstName,
            'lastname' => $this->faker->lastName,
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => '$2y$10$Jo981kE7L3V/b/9yb4pd2.eCogVKrzzJ1UHydxIfsCFkHOE3DnwBC',
            'age' => $this->faker->numberBetween(18,60),
            'gender_id' => $this->faker->numberBetween(1,2),
            'position_id' => \App\Models\Position::all()->random()->id,
            'role_id' => \App\Models\Role::all()->random()->id,
            'applicant_id' => \App\Models\Applicant::all()->random()->id,
            'store_id' => \App\Models\Store::all()->random()->id,
            'division_id' => \App\Models\Division::all()->random()->id,
            'region_id' => \App\Models\Region::all()->random()->id,
            'brand_id' => \App\Models\Brand::all()->random()->id,
            'internal' => $this->faker->numberBetween(1,2),
            'status_id' => \App\Models\Status::all()->random()->id,
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function unverified()
    {
        return $this->state(function (array $attributes) {
            return [
                'email_verified_at' => null,
            ];
        });
    }
}
