<?php

namespace Database\Factories;

use App\Models\CategoriaMovimiento;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CategoriaMovimiento>
 */
class CategoriaMovimientoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => $this->faker->unique()->words(2, true),
            'tipo' => $this->faker->randomElement(['ingreso', 'egreso']),
            'estado' => true,
        ];
    }
}
