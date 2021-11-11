<?php

namespace Database\Factories;

use App\Model;
use App\Models\Marca;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MarcaFactory extends Factory
{
    protected $model = Marca::class;

    public function definition(): array
    {
    	return [
    	    "nombre" => $this->faker->name,
            "empresaid" => 2
    	];
    }
}
