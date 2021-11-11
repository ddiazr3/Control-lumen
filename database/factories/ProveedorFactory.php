<?php

namespace Database\Factories;

use App\Model;
use App\Models\Proveedor;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProveedorFactory extends Factory
{
    protected $model = Proveedor::class;

    public function definition(): array
    {
    	return [
    	    "nombre" => $this->faker->name,
            "direccion" => $this->faker->address,
            "telefono" => $this->faker->phoneNumber,
            "empresaid" => 2
    	];
    }
}
