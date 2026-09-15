<?php

namespace Database\Factories;

use App\Models\CuentaPorPagar;
use App\Models\PagoProveedor;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PagoProveedor>
 */
class PagoProveedorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cuenta_por_pagar_id' => CuentaPorPagar::factory(),
            'monto' => $this->faker->randomFloat(2, 10, 100),
            'fecha_pago' => now(),
            'medio_pago' => $this->faker->randomElement(['efectivo', 'tarjeta', 'transferencia', 'yape_plin']),
            'user_id' => User::factory(),
        ];
    }
}
