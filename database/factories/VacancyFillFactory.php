<?php

namespace Database\Factories;

use App\Models\VacancyFill;
use Illuminate\Database\Eloquent\Factories\Factory;

class VacancyFillFactory extends Factory
{
    protected $model = VacancyFill::class;

    public function definition()
    {
        return [    
            'applicant_id' => function () {
                return \App\Models\Applicant::factory()->create()->id;
            },
            'approved' => 'Yes',
            'sap_number' => '12345678',
            'vacancy_id' => function () {
                return \App\Models\Vacancy::factory()->create()->id;
            },
        ];
    }
}