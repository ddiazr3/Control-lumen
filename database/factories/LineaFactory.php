<?php

namespace Database\Factories;

use App\Model;
use App\Models\Linea;
use Illuminate\Database\Eloquent\Factories\Factory;

class LineaFactory extends Factory
{
    protected $model = Linea::class;

    public function definition(): array
    {
    	return [
    	    "nombre" => $this->faker->name,
            "empresaid" => 2,
            "marcaid" => 501
    	];
    }
}
