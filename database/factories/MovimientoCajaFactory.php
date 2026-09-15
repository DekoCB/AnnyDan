<?php

namespace Database\Factories;

use App\Models\CategoriaMovimiento;
use App\Models\MovimientoCaja;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MovimientoCaja>
 */
class MovimientoCajaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $tipo = $this->faker->randomElement(['ingreso', 'egreso']);

        return [
            'tipo' => $tipo,
            'categoria_id' => CategoriaMovimiento::factory()->state(['tipo' => $tipo]),
            'monto' => $this->faker->randomFloat(2, 10, 500),
            'fecha' => now(),
            'descripcion' => $this->faker->sentence(4),
            'user_id' => User::factory(),
        ];
    }
}
