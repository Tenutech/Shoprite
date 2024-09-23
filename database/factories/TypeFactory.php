<?php

namespace Database\Factories;

use App\Models\Type;
use Illuminate\Database\Eloquent\Factories\Factory;

class TypeFactory extends Factory
{
    protected $model = Type::class;

    public function definition()
    {
        return [
            'name' => 'Full Time',
            'icon' => 'ri-briefcase-2-line',
            'lordicon' => 'https://cdn.lordicon.com/xzalkbkz.json',
            'color' => 'success',
            'created_at' => '2023-10-05 09:10:24',
            'updated_at' => '2023-10-05 09:10:24',
        ];
    }
}