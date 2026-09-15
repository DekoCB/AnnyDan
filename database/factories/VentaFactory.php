<?php

namespace Database\Factories;

use App\Models\Cliente;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Venta>
 */
class VentaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = $this->faker->randomFloat(2, 10, 500);
        $impuestos = round($subtotal * 0.18, 2);

        return [
            'numero_operacion' => 'V-'.$this->faker->unique()->numerify('######'),
            'cliente_id' => Cliente::factory(),
            'user_id' => User::factory(),
            'fecha' => now(),
            'subtotal' => $subtotal,
            'impuestos' => $impuestos,
            'total' => $subtotal + $impuestos,
            'forma_pago' => $this->faker->randomElement(['efectivo', 'tarjeta', 'transferencia', 'yape_plin']),
            'estado' => 'registrada',
        ];
    }
}
