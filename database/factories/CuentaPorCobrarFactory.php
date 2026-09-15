<?php

namespace Database\Factories;

use App\Models\Cliente;
use App\Models\CuentaPorCobrar;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CuentaPorCobrar>
 */
class CuentaPorCobrarFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cliente_id' => Cliente::factory(),
            'venta_id' => null,
            'monto_total' => $this->faker->randomFloat(2, 50, 1000),
            'monto_pagado' => 0,
            'fecha_emision' => now(),
            'fecha_vencimiento' => now()->addDays(30),
            'estado' => 'pendiente',
        ];
    }
}
