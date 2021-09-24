<?php

namespace Database\Factories;

use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UsuarioFactory extends Factory
{
    protected $model = Usuario::class;

    public function definition()
    {
    	return [
            "nombre"    => $this->faker->name,
            "apellido"  => $this->faker->lastName,
            "dpi"       => $this->faker->phoneNumber,
            "telefono"  => $this->faker->phoneNumber,
            "direccion" => $this->faker->streetAddress,
    	];
    }
}
