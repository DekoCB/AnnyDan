<?php

namespace Tests\Feature;

use App\Models\CuentaPorPagar;
use App\Models\Proveedor;
use App\Models\User;
use Database\Seeders\CategoriasMovimientoSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CuentaPorPagarTest extends TestCase
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
        $this->get(route('cuentas-pagar.index'))->assertRedirect(route('login'));
    }

    public function test_cajero_has_no_access_to_cuentas_por_pagar(): void
    {
        $this->actingAs($this->cajero)->get(route('cuentas-pagar.index'))->assertForbidden();
    }

    public function test_contabilidad_can_view_but_not_create(): void
    {
        $this->actingAs($this->contabilidad)->get(route('cuentas-pagar.index'))->assertOk();
        $this->actingAs($this->contabilidad)->get(route('cuentas-pagar.create'))->assertForbidden();
    }

    public function test_ac01_creating_a_cuenta_por_pagar_registers_the_correct_amount(): void
    {
        $proveedor = Proveedor::factory()->create(['estado' => true]);

        $response = $this->actingAs($this->administrativo)->post(route('cuentas-pagar.store'), [
            'proveedor_id' => $proveedor->id,
            'documento_origen' => 'F001-0001',
            'monto_total' => 1000,
            'fecha_vencimiento' => now()->addDays(15)->toDateString(),
        ]);

        $cuenta = CuentaPorPagar::first();

        $response->assertRedirect(route('cuentas-pagar.show', $cuenta));
        $this->assertSame('1000.00', $cuenta->monto_total);
        $this->assertSame('pendiente', $cuenta->estado);
        $this->assertDatabaseHas('auditorias', ['accion' => 'cuenta_por_pagar.creada', 'auditable_id' => $cuenta->id]);
    }

    public function test_ac02_registering_a_payment_reduces_the_balance(): void
    {
        $cuenta = CuentaPorPagar::factory()->create(['monto_total' => 1000, 'monto_pagado' => 0]);

        $this->actingAs($this->administrativo)->post(route('cuentas-pagar.pagar', $cuenta), [
            'monto' => 300,
            'fecha_pago' => now()->toDateString(),
            'medio_pago' => 'transferencia',
        ])->assertRedirect(route('cuentas-pagar.show', $cuenta));

        $cuenta->refresh();

        $this->assertSame('300.00', $cuenta->monto_pagado);
        $this->assertSame('700.00', $cuenta->saldo);
        $this->assertSame('parcial', $cuenta->estado);
    }

    public function test_ac03_a_payment_larger_than_the_balance_is_rejected(): void
    {
        $cuenta = CuentaPorPagar::factory()->create(['monto_total' => 1000, 'monto_pagado' => 300]);

        $this->actingAs($this->administrativo)->post(route('cuentas-pagar.pagar', $cuenta), [
            'monto' => 800,
            'fecha_pago' => now()->toDateString(),
            'medio_pago' => 'transferencia',
        ])->assertSessionHasErrors('monto');

        $cuenta->refresh();
        $this->assertSame('300.00', $cuenta->monto_pagado);
    }

    public function test_ac04_reaching_zero_balance_marks_the_account_as_pagada(): void
    {
        $cuenta = CuentaPorPagar::factory()->create(['monto_total' => 1000, 'monto_pagado' => 700]);

        $this->actingAs($this->administrativo)->post(route('cuentas-pagar.pagar', $cuenta), [
            'monto' => 300,
            'fecha_pago' => now()->toDateString(),
            'medio_pago' => 'transferencia',
        ]);

        $cuenta->refresh();

        $this->assertSame('1000.00', $cuenta->monto_pagado);
        $this->assertSame('pagada', $cuenta->estado);
    }

    public function test_administrativo_can_anular_a_pendiente_account_with_no_payments(): void
    {
        $cuenta = CuentaPorPagar::factory()->create(['estado' => 'pendiente', 'monto_pagado' => 0]);

        $this->actingAs($this->administrativo)
            ->patch(route('cuentas-pagar.anular', $cuenta))
            ->assertRedirect(route('cuentas-pagar.show', $cuenta));

        $this->assertDatabaseHas('cuentas_por_pagar', ['id' => $cuenta->id, 'estado' => 'anulada']);
    }

    public function test_cannot_anular_an_account_with_payments_already_registered(): void
    {
        $cuenta = CuentaPorPagar::factory()->create(['estado' => 'parcial', 'monto_total' => 1000, 'monto_pagado' => 300]);

        $this->actingAs($this->administrativo)
            ->patch(route('cuentas-pagar.anular', $cuenta))
            ->assertRedirect();

        $this->assertDatabaseHas('cuentas_por_pagar', ['id' => $cuenta->id, 'estado' => 'parcial']);
    }
}
