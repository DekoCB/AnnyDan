<?php

namespace Tests\Feature;

use App\Models\Comprobante;
use App\Models\User;
use App\Models\Venta;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ComprobanteTest extends TestCase
{
    use RefreshDatabase;

    private User $cajero;

    private User $contabilidad;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->cajero = User::factory()->create();
        $this->cajero->assignRole('CAJERO');

        $this->contabilidad = User::factory()->create();
        $this->contabilidad->assignRole('CONTABILIDAD');
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('comprobantes.index'))->assertRedirect(route('login'));
    }

    public function test_contabilidad_can_view_but_not_emit(): void
    {
        $venta = Venta::factory()->create(['estado' => 'registrada']);

        $this->actingAs($this->contabilidad)->get(route('comprobantes.index'))->assertOk();
        $this->actingAs($this->contabilidad)->post(route('comprobantes.store', $venta))->assertForbidden();
    }

    public function test_cajero_can_emit_a_boleta_for_a_registrada_venta(): void
    {
        $venta = Venta::factory()->create(['estado' => 'registrada']);

        $response = $this->actingAs($this->cajero)->post(route('comprobantes.store', $venta));

        $comprobante = Comprobante::first();

        $response->assertRedirect(route('comprobantes.show', $comprobante));
        $this->assertSame('boleta', $comprobante->tipo);
        $this->assertSame('B001', $comprobante->serie);
        $this->assertSame('emitido', $comprobante->estado);
        $this->assertSame($venta->id, $comprobante->venta_id);
        $this->assertSame('B001-00000001', $comprobante->numero_completo);
        $this->assertDatabaseHas('auditorias', ['accion' => 'comprobante.emitido', 'auditable_id' => $comprobante->id]);
    }

    public function test_numero_correlativo_increments_across_boletas(): void
    {
        $ventaUno = Venta::factory()->create(['estado' => 'registrada']);
        $ventaDos = Venta::factory()->create(['estado' => 'registrada']);

        $this->actingAs($this->cajero)->post(route('comprobantes.store', $ventaUno));
        $this->actingAs($this->cajero)->post(route('comprobantes.store', $ventaDos));

        $correlativos = Comprobante::orderBy('id')->pluck('numero_correlativo');

        $this->assertSame([1, 2], $correlativos->toArray());
    }

    public function test_cannot_emit_a_second_boleta_for_the_same_venta(): void
    {
        $venta = Venta::factory()->create(['estado' => 'registrada']);
        Comprobante::factory()->create(['venta_id' => $venta->id]);

        $this->actingAs($this->cajero)->post(route('comprobantes.store', $venta))->assertRedirect();

        $this->assertSame(1, Comprobante::where('venta_id', $venta->id)->count());
    }

    public function test_cannot_emit_a_boleta_for_an_anulada_venta(): void
    {
        $venta = Venta::factory()->create(['estado' => 'anulada']);

        $this->actingAs($this->cajero)->post(route('comprobantes.store', $venta))->assertRedirect();

        $this->assertDatabaseCount('comprobantes', 0);
    }

    public function test_cajero_can_anular_a_comprobante(): void
    {
        $comprobante = Comprobante::factory()->create(['estado' => 'emitido']);

        $this->actingAs($this->cajero)
            ->patch(route('comprobantes.anular', $comprobante))
            ->assertRedirect(route('comprobantes.show', $comprobante));

        $this->assertDatabaseHas('comprobantes', ['id' => $comprobante->id, 'estado' => 'anulado']);
    }

    public function test_contabilidad_cannot_anular_a_comprobante(): void
    {
        $comprobante = Comprobante::factory()->create(['estado' => 'emitido']);

        $this->actingAs($this->contabilidad)
            ->patch(route('comprobantes.anular', $comprobante))
            ->assertForbidden();
    }

    public function test_an_already_anulado_comprobante_cannot_be_anulado_again(): void
    {
        $comprobante = Comprobante::factory()->create(['estado' => 'anulado']);

        $this->actingAs($this->cajero)->patch(route('comprobantes.anular', $comprobante))->assertRedirect();

        $this->assertDatabaseMissing('auditorias', ['accion' => 'comprobante.anulado', 'auditable_id' => $comprobante->id]);
    }
}
