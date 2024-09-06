<?php

namespace Database\Factories;

use App\Models\Interview;
use App\Models\Applicant;
use App\Models\User;
use App\Models\Vacancy;
use Illuminate\Database\Eloquent\Factories\Factory;

class InterviewFactory extends Factory
{
    protected $model = Interview::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'applicant_id' => Applicant::factory(),
            'interviewer_id' => User::factory(),
            'vacancy_id' => Vacancy::factory(), 
            'scheduled_date' => $this->faker->dateTimeBetween('+1 week', '+1 month'),
            'start_time' => '12:00:00',
            'end_time' => '13:00:00',
            'location' => 'Stellenbosch',
            'status' => $this->faker->randomElement(['Scheduled']),
            'score' => $this->faker->optional()->randomFloat(1, 0, 5),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}