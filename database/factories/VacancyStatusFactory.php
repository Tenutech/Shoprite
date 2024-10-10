<?php

namespace Database\Factories;

use App\Models\VacancyStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

class VacancyStatusFactory extends Factory
{
    protected $model = VacancyStatus::class;

    public function definition()
    {
        return [
            'name' => 'Active',     
            'icon' => 'circle',
            'color' => '#000000',
        ];
    }
}