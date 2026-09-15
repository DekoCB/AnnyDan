<?php

namespace Database\Factories;

use App\Models\Comprobante;
use App\Models\Venta;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Comprobante>
 */
class ComprobanteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'venta_id' => Venta::factory(),
            'tipo' => 'boleta',
            'serie' => 'B001',
            'numero_correlativo' => $this->faker->unique()->numberBetween(1, 999999),
            'fecha_emision' => now(),
            'estado' => 'emitido',
        ];
    }
}
