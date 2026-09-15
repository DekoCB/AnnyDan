<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\User;
use App\Models\Venta;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReporteVentaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('reportes.ventas'))->assertRedirect(route('login'));
    }

    public function test_cajero_cannot_view_reportes(): void
    {
        $cajero = User::factory()->create();
        $cajero->assignRole('CAJERO');

        $this->actingAs($cajero)->get(route('reportes.ventas'))->assertForbidden();
    }

    public function test_reporte_aggregates_ventas_for_the_selected_month(): void
    {
        $administrativo = User::factory()->create();
        $administrativo->assignRole('ADMINISTRATIVO');

        $cliente = Cliente::factory()->create(['nombre_razon_social' => 'Cliente Uno']);

        Venta::factory()->create([
            'cliente_id' => $cliente->id,
            'total' => 100,
            'forma_pago' => 'efectivo',
            'estado' => 'registrada',
            'fecha' => now(),
        ]);
        Venta::factory()->create([
            'cliente_id' => $cliente->id,
            'total' => 50,
            'forma_pago' => 'efectivo',
            'estado' => 'registrada',
            'fecha' => now(),
        ]);
        // Fuera del mes actual: no debe contarse.
        Venta::factory()->create(['total' => 999, 'estado' => 'registrada', 'fecha' => now()->subMonths(3)]);
        // Anulada: no debe contarse.
        Venta::factory()->create(['total' => 999, 'estado' => 'anulada', 'fecha' => now()]);

        $response = $this->actingAs($administrativo)->get(route('reportes.ventas', [
            'mes' => now()->month,
            'anio' => now()->year,
        ]));

        $response->assertOk();
        $response->assertViewHas('totalVentas', fn ($valor) => (float) $valor === 150.0);
        $response->assertViewHas('numeroOperaciones', 2);
        $response->assertViewHas('ticketPromedio', fn ($valor) => (float) $valor === 75.0);
    }
}
