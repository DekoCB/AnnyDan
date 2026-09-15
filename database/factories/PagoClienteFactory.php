<?php

namespace Database\Factories;

use App\Models\CuentaPorCobrar;
use App\Models\PagoCliente;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PagoCliente>
 */
class PagoClienteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cuenta_por_cobrar_id' => CuentaPorCobrar::factory(),
            'monto' => $this->faker->randomFloat(2, 10, 100),
            'fecha_pago' => now(),
            'medio_pago' => $this->faker->randomElement(['efectivo', 'tarjeta', 'transferencia', 'yape_plin']),
            'user_id' => User::factory(),
        ];
    }
}
