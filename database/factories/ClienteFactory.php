<?php

namespace Database\Factories;

use App\Models\Cliente;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Cliente>
 */
class ClienteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tipo_documento' => 'DNI',
            'numero_documento' => $this->faker->unique()->numerify('########'),
            'nombre_razon_social' => $this->faker->company(),
            'direccion' => $this->faker->address(),
            'telefono' => $this->faker->numerify('9########'),
            'email' => $this->faker->unique()->safeEmail(),
            'estado' => true,
        ];
    }
}
