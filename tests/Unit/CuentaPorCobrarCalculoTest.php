<?php

namespace Tests\Unit;

use App\Models\CuentaPorCobrar;
use Tests\TestCase;

class CuentaPorCobrarCalculoTest extends TestCase
{
    /**
     * Un TestCase de PHPUnit puro no arranca el framework, y el cast
     * 'date' de Eloquent necesita una conexión resuelta (getDateFormat()
     * la consulta) incluso solo para leer el atributo — por eso este test
     * extiende el TestCase de Laravel en vez del de PHPUnit, aunque no
     * toque la base de datos.
     */
    public function test_saldo_es_monto_total_menos_monto_pagado(): void
    {
        $cuenta = new CuentaPorCobrar(['monto_total' => 1000, 'monto_pagado' => 300]);

        $this->assertSame('700.00', $cuenta->saldo);
    }

    public function test_esta_vencida_cuando_paso_la_fecha_y_hay_saldo_pendiente(): void
    {
        $cuenta = new CuentaPorCobrar([
            'monto_total' => 1000,
            'monto_pagado' => 0,
            'estado' => 'pendiente',
            'fecha_vencimiento' => now()->subDay(),
        ]);

        $this->assertTrue($cuenta->estaVencida());
        $this->assertSame('vencida', $cuenta->estado_mostrado);
    }

    public function test_no_esta_vencida_antes_de_la_fecha_de_vencimiento(): void
    {
        $cuenta = new CuentaPorCobrar([
            'monto_total' => 1000,
            'monto_pagado' => 0,
            'estado' => 'pendiente',
            'fecha_vencimiento' => now()->addDay(),
        ]);

        $this->assertFalse($cuenta->estaVencida());
        $this->assertSame('pendiente', $cuenta->estado_mostrado);
    }

    public function test_una_cuenta_pagada_nunca_se_muestra_como_vencida(): void
    {
        $cuenta = new CuentaPorCobrar([
            'monto_total' => 1000,
            'monto_pagado' => 1000,
            'estado' => 'pagada',
            'fecha_vencimiento' => now()->subDay(),
        ]);

        $this->assertFalse($cuenta->estaVencida());
        $this->assertSame('pagada', $cuenta->estado_mostrado);
    }
}
