<?php

namespace Database\Factories;

use App\Models\CuentaPorPagar;
use App\Models\Proveedor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CuentaPorPagar>
 */
class CuentaPorPagarFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'proveedor_id' => Proveedor::factory(),
            'documento_origen' => 'F001-'.$this->faker->unique()->numerify('####'),
            'monto_total' => $this->faker->randomFloat(2, 50, 1000),
            'monto_pagado' => 0,
            'fecha_emision' => now(),
            'fecha_vencimiento' => now()->addDays(30),
            'estado' => 'pendiente',
        ];
    }
}
