<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Venta;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_administrativo_sees_ventas_and_cuentas_sections(): void
    {
        $administrativo = User::factory()->create();
        $administrativo->assignRole('ADMINISTRATIVO');

        Venta::factory()->create(['total' => 150, 'estado' => 'registrada']);

        $response = $this->actingAs($administrativo)->get(route('dashboard'));

        $response->assertOk();
        $response->assertViewHas('totalVentasMes');
        $response->assertViewHas('cxcPendiente');
        $response->assertViewHas('cxpPendiente');
    }

    public function test_cajero_does_not_see_cuentas_por_pagar_section(): void
    {
        $cajero = User::factory()->create();
        $cajero->assignRole('CAJERO');

        $response = $this->actingAs($cajero)->get(route('dashboard'));

        $response->assertOk();
        $response->assertViewHas('totalVentasMes');
        $response->assertViewMissing('cxpPendiente');
    }

    public function test_ventas_del_mes_excludes_anuladas(): void
    {
        $administrativo = User::factory()->create();
        $administrativo->assignRole('ADMINISTRATIVO');

        Venta::factory()->create(['total' => 100, 'estado' => 'registrada', 'fecha' => now()]);
        Venta::factory()->create(['total' => 999, 'estado' => 'anulada', 'fecha' => now()]);

        $response = $this->actingAs($administrativo)->get(route('dashboard'));

        $response->assertViewHas('totalVentasMes', fn ($valor) => (float) $valor === 100.0);
    }
}
