<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\CuentaPorCobrar;
use App\Models\User;
use App\Models\Venta;
use Database\Seeders\CategoriasMovimientoSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CuentaPorCobrarTest extends TestCase
{
    use RefreshDatabase;

    private User $administrativo;

    private User $cajero;

    private User $contabilidad;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(CategoriasMovimientoSeeder::class);

        $this->administrativo = User::factory()->create();
        $this->administrativo->assignRole('ADMINISTRATIVO');

        $this->cajero = User::factory()->create();
        $this->cajero->assignRole('CAJERO');

        $this->contabilidad = User::factory()->create();
        $this->contabilidad->assignRole('CONTABILIDAD');
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('cuentas-cobrar.index'))->assertRedirect(route('login'));
    }

    public function test_contabilidad_can_view_but_not_create(): void
    {
        $this->actingAs($this->contabilidad)->get(route('cuentas-cobrar.index'))->assertOk();
        $this->actingAs($this->contabilidad)->get(route('cuentas-cobrar.create'))->assertForbidden();
    }

    /**
     * AC-01: dado un cliente registrado, al crear una cuenta por cobrar
     * debe registrarse correctamente el monto total.
     */
    public function test_ac01_creating_a_standalone_cuenta_por_cobrar_registers_the_correct_amount(): void
    {
        $cliente = Cliente::factory()->create(['estado' => true]);

        $response = $this->actingAs($this->administrativo)->post(route('cuentas-cobrar.store'), [
            'cliente_id' => $cliente->id,
            'monto_total' => 1000,
            'fecha_vencimiento' => now()->addDays(15)->toDateString(),
        ]);

        $cuenta = CuentaPorCobrar::first();

        $response->assertRedirect(route('cuentas-cobrar.show', $cuenta));
        $this->assertSame('1000.00', $cuenta->monto_total);
        $this->assertSame('0.00', $cuenta->monto_pagado);
        $this->assertSame('pendiente', $cuenta->estado);
    }

    public function test_creating_from_a_venta_uses_the_ventas_total_not_the_submitted_amount(): void
    {
        $venta = Venta::factory()->create(['total' => 500]);

        $this->actingAs($this->administrativo)->post(route('cuentas-cobrar.store'), [
            'venta_id' => $venta->id,
            'cliente_id' => $venta->cliente_id,
            'monto_total' => 1, // intento de manipular el monto — debe ser ignorado
            'fecha_vencimiento' => now()->addDays(15)->toDateString(),
        ]);

        $cuenta = CuentaPorCobrar::first();

        $this->assertSame('500.00', $cuenta->monto_total);
        $this->assertSame($venta->id, $cuenta->venta_id);
    }

    public function test_a_venta_cannot_have_two_cuentas_por_cobrar(): void
    {
        $venta = Venta::factory()->create();
        CuentaPorCobrar::factory()->create(['venta_id' => $venta->id]);

        $this->actingAs($this->administrativo)
            ->post(route('cuentas-cobrar.store'), [
                'venta_id' => $venta->id,
                'cliente_id' => $venta->cliente_id,
                'monto_total' => 100,
                'fecha_vencimiento' => now()->addDays(15)->toDateString(),
            ])
            ->assertSessionHasErrors('venta_id');
    }

    /**
     * AC-02: dado un saldo de S/1,000, cuando se registra un pago de
     * S/300, el saldo debe quedar en S/700.
     */
    public function test_ac02_registering_a_payment_reduces_the_balance(): void
    {
        $cuenta = CuentaPorCobrar::factory()->create(['monto_total' => 1000, 'monto_pagado' => 0]);

        $this->actingAs($this->cajero)->post(route('cuentas-cobrar.pagar', $cuenta), [
            'monto' => 300,
            'fecha_pago' => now()->toDateString(),
            'medio_pago' => 'efectivo',
        ])->assertRedirect(route('cuentas-cobrar.show', $cuenta));

        $cuenta->refresh();

        $this->assertSame('300.00', $cuenta->monto_pagado);
        $this->assertSame('700.00', $cuenta->saldo);
        $this->assertSame('parcial', $cuenta->estado);
    }

    /**
     * AC-03: dado un saldo de S/700, cuando se intenta registrar un pago
     * de S/800, el sistema debe rechazar la operación.
     */
    public function test_ac03_a_payment_larger_than_the_balance_is_rejected(): void
    {
        $cuenta = CuentaPorCobrar::factory()->create(['monto_total' => 1000, 'monto_pagado' => 300]);

        $this->actingAs($this->cajero)->post(route('cuentas-cobrar.pagar', $cuenta), [
            'monto' => 800,
            'fecha_pago' => now()->toDateString(),
            'medio_pago' => 'efectivo',
        ])->assertSessionHasErrors('monto');

        $cuenta->refresh();
        $this->assertSame('300.00', $cuenta->monto_pagado);
    }

    /**
     * AC-04: cuando una cuenta llega a saldo 0, su estado debe cambiar a
     * PAGADA.
     */
    public function test_ac04_reaching_zero_balance_marks_the_account_as_pagada(): void
    {
        $cuenta = CuentaPorCobrar::factory()->create(['monto_total' => 1000, 'monto_pagado' => 700]);

        $this->actingAs($this->cajero)->post(route('cuentas-cobrar.pagar', $cuenta), [
            'monto' => 300,
            'fecha_pago' => now()->toDateString(),
            'medio_pago' => 'efectivo',
        ]);

        $cuenta->refresh();

        $this->assertSame('1000.00', $cuenta->monto_pagado);
        $this->assertSame('0.00', $cuenta->saldo);
        $this->assertSame('pagada', $cuenta->estado);
    }

    public function test_cannot_pay_an_already_pagada_account(): void
    {
        $cuenta = CuentaPorCobrar::factory()->create(['monto_total' => 100, 'monto_pagado' => 100, 'estado' => 'pagada']);

        $this->actingAs($this->cajero)->post(route('cuentas-cobrar.pagar', $cuenta), [
            'monto' => 10,
            'fecha_pago' => now()->toDateString(),
            'medio_pago' => 'efectivo',
        ])->assertRedirect();

        $cuenta->refresh();
        $this->assertSame('100.00', $cuenta->monto_pagado);
    }

    public function test_cajero_cannot_create_or_anular_cuentas_por_cobrar(): void
    {
        $this->actingAs($this->cajero)->get(route('cuentas-cobrar.create'))->assertForbidden();

        $cuenta = CuentaPorCobrar::factory()->create();
        $this->actingAs($this->cajero)->patch(route('cuentas-cobrar.anular', $cuenta))->assertForbidden();
    }

    public function test_administrativo_can_anular_a_pendiente_account_with_no_payments(): void
    {
        $cuenta = CuentaPorCobrar::factory()->create(['estado' => 'pendiente', 'monto_pagado' => 0]);

        $this->actingAs($this->administrativo)
            ->patch(route('cuentas-cobrar.anular', $cuenta))
            ->assertRedirect(route('cuentas-cobrar.show', $cuenta));

        $this->assertDatabaseHas('cuentas_por_cobrar', ['id' => $cuenta->id, 'estado' => 'anulada']);
    }

    public function test_cannot_anular_an_account_with_payments_already_registered(): void
    {
        $cuenta = CuentaPorCobrar::factory()->create(['estado' => 'parcial', 'monto_total' => 1000, 'monto_pagado' => 300]);

        $this->actingAs($this->administrativo)
            ->patch(route('cuentas-cobrar.anular', $cuenta))
            ->assertRedirect();

        $this->assertDatabaseHas('cuentas_por_cobrar', ['id' => $cuenta->id, 'estado' => 'parcial']);
    }
}
