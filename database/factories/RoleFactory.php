<?php

namespace Database\Factories;

use App\Models\Role;
use App\Models\User;
se Illuminate\Database\Eloquent\Factories\Factory;

class RoleFactory extends Factory
{
    protected $model = Role::class;

    public function definition()
    {
        return [
            'name' => $this->faker->firstName,
            'icon' => $this->faker->word,
        ];
    }
}