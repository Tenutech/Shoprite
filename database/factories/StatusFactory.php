<?php

namespace Database\Factories;

use App\Models\Status;
use Illuminate\Database\Eloquent\Factories\Factory;

class StatusFactory extends Factory
{
    protected $model = Status::class;

    public function definition()
    {
        return [
<<<<<<< HEAD
            'name' => 'Active',     
            'icon' => 'circle',
=======
            'name' => 'Open',  
            'icon' => 'man',
>>>>>>> 51f333a (SS-4 | Add field for position detail)
            'color' => '#000000',
        ];
    }
}