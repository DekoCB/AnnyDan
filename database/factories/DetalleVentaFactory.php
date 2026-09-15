<?php

namespace Database\Factories;

use App\Models\DetalleVenta;
use App\Models\Venta;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DetalleVenta>
 */
class DetalleVentaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $cantidad = $this->faker->randomFloat(2, 1, 10);
        $precioUnitario = $this->faker->randomFloat(2, 5, 100);

        return [
            'venta_id' => Venta::factory(),
            'descripcion' => $this->faker->words(3, true),
            'cantidad' => $cantidad,
            'precio_unitario' => $precioUnitario,
            'subtotal' => round($cantidad * $precioUnitario, 2),
        ];
    }
}
