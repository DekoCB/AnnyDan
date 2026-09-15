<?php

namespace Tests\Feature;

use App\Models\CategoriaMovimiento;
use App\Models\CuentaPorCobrar;
use App\Models\CuentaPorPagar;
use App\Models\MovimientoCaja;
use App\Models\PagoCliente;
use App\Models\PagoProveedor;
use App\Models\User;
use App\Models\Venta;
use Database\Seeders\CategoriasMovimientoSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MovimientoCajaTest extends TestCase
{
    use RefreshDatabase;

    private User $cajero;

    private User $administrativo;

    private User $contabilidad;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(CategoriasMovimientoSeeder::class);

        $this->cajero = User::factory()->create();
        $this->cajero->assignRole('CAJERO');

        $this->administrativo = User::factory()->create();
        $this->administrativo->assignRole('ADMINISTRATIVO');

        $this->contabilidad = User::factory()->create();
        $this->contabilidad->assignRole('CONTABILIDAD');
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('caja.index'))->assertRedirect(route('login'));
    }

    public function test_contabilidad_can_view_but_not_register_movimientos(): void
    {
        $this->actingAs($this->contabilidad)->get(route('caja.index'))->assertOk();
        $this->actingAs($this->contabilidad)->get(route('caja.create'))->assertForbidden();
    }

    public function test_registering_a_client_payment_creates_an_ingreso_movimiento(): void
    {
        $cuenta = CuentaPorCobrar::factory()->create(['monto_total' => 500, 'monto_pagado' => 0]);

        $this->actingAs($this->cajero)->post(route('cuentas-cobrar.pagar', $cuenta), [
            'monto' => 200,
            'fecha_pago' => now()->toDateString(),
            'medio_pago' => 'efectivo',
        ]);

        $this->assertDatabaseHas('movimientos_caja', [
            'tipo' => 'ingreso',
            'monto' => 200,
            'origen_type' => PagoCliente::class,
        ]);
    }

    public function test_registering_a_proveedor_payment_creates_an_egreso_movimiento(): void
    {
        $cuenta = CuentaPorPagar::factory()->create(['monto_total' => 500, 'monto_pagado' => 0]);

        $this->actingAs($this->administrativo)->post(route('cuentas-pagar.pagar', $cuenta), [
            'monto' => 150,
            'fecha_pago' => now()->toDateString(),
            'medio_pago' => 'transferencia',
        ]);

        $this->assertDatabaseHas('movimientos_caja', [
            'tipo' => 'egreso',
            'monto' => 150,
            'origen_type' => PagoProveedor::class,
        ]);
    }

    public function test_creating_a_venta_does_not_create_any_movimiento_caja(): void
    {
        // Venta ≠ ingreso de caja: registrar una venta por sí sola no debe
        // generar ningún movimiento (sección 11 del requerimiento).
        Venta::factory()->create();

        $this->assertDatabaseCount('movimientos_caja', 0);
    }

    public function test_cajero_can_register_a_manual_ingreso(): void
    {
        $categoria = CategoriaMovimiento::where('tipo', 'ingreso')->first();

        $response = $this->actingAs($this->cajero)->post(route('caja.store'), [
            'tipo' => 'ingreso',
            'categoria_id' => $categoria->id,
            'monto' => 100,
            'fecha' => now()->toDateString(),
            'descripcion' => 'Ingreso manual de prueba',
        ]);

        $response->assertRedirect(route('caja.index'));
        $this->assertDatabaseHas('movimientos_caja', ['tipo' => 'ingreso', 'monto' => 100]);
    }

    public function test_categoria_must_match_the_submitted_tipo(): void
    {
        $categoriaEgreso = CategoriaMovimiento::where('tipo', 'egreso')->first();

        $this->actingAs($this->cajero)->post(route('caja.store'), [
            'tipo' => 'ingreso',
            'categoria_id' => $categoriaEgreso->id,
            'monto' => 100,
            'fecha' => now()->toDateString(),
        ])->assertSessionHasErrors('categoria_id');
    }

    public function test_period_totals_only_include_movimientos_within_range(): void
    {
        MovimientoCaja::factory()->create(['tipo' => 'ingreso', 'monto' => 100, 'fecha' => now()]);
        MovimientoCaja::factory()->create(['tipo' => 'ingreso', 'monto' => 999, 'fecha' => now()->subMonths(2)]);
        MovimientoCaja::factory()->create(['tipo' => 'egreso', 'monto' => 40, 'fecha' => now()]);

        $response = $this->actingAs($this->cajero)->get(route('caja.index', [
            'desde' => now()->startOfMonth()->toDateString(),
            'hasta' => now()->endOfMonth()->toDateString(),
        ]));

        $response->assertOk();
        $response->assertViewHas('totalIngresos', fn ($valor) => (float) $valor === 100.0);
        $response->assertViewHas('totalEgresos', fn ($valor) => (float) $valor === 40.0);
        $response->assertViewHas('utilidad', fn ($valor) => (float) $valor === 60.0);
    }
}
