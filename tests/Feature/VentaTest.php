<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Comprobante;
use App\Models\CuentaPorCobrar;
use App\Models\User;
use App\Models\Venta;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VentaTest extends TestCase
{
    use RefreshDatabase;

    private User $administrativo;

    private User $cajero;

    private User $contabilidad;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->administrativo = User::factory()->create();
        $this->administrativo->assignRole('ADMINISTRATIVO');

        $this->cajero = User::factory()->create();
        $this->cajero->assignRole('CAJERO');

        $this->contabilidad = User::factory()->create();
        $this->contabilidad->assignRole('CONTABILIDAD');
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('ventas.index'))->assertRedirect(route('login'));
    }

    public function test_contabilidad_cannot_create_ventas(): void
    {
        $this->actingAs($this->contabilidad)
            ->get(route('ventas.create'))
            ->assertForbidden();
    }

    public function test_cajero_can_view_and_create_ventas(): void
    {
        $this->actingAs($this->cajero)->get(route('ventas.index'))->assertOk();
        $this->actingAs($this->cajero)->get(route('ventas.create'))->assertOk();
    }

    public function test_creating_a_venta_computes_totals_on_the_backend(): void
    {
        $cliente = Cliente::factory()->create(['estado' => true]);

        $response = $this->actingAs($this->cajero)->post(route('ventas.store'), [
            'cliente_id' => $cliente->id,
            'forma_pago' => 'efectivo',
            'detalles' => [
                ['descripcion' => 'Producto A', 'cantidad' => 2, 'precio_unitario' => 50],
                ['descripcion' => 'Producto B', 'cantidad' => 1, 'precio_unitario' => 30],
            ],
        ]);

        $venta = Venta::first();

        $response->assertRedirect(route('ventas.show', $venta));

        // subtotal = 2*50 + 1*30 = 130.00; IGV 18% = 23.40; total = 153.40
        $this->assertSame('130.00', $venta->subtotal);
        $this->assertSame('23.40', $venta->impuestos);
        $this->assertSame('153.40', $venta->total);
        $this->assertSame('registrada', $venta->estado);
        $this->assertCount(2, $venta->detalles);
        $this->assertStringStartsWith('V-', $venta->numero_operacion);
        $this->assertDatabaseHas('auditorias', ['accion' => 'venta.creada', 'auditable_id' => $venta->id]);
    }

    public function test_numero_operacion_increments_across_ventas(): void
    {
        $cliente = Cliente::factory()->create(['estado' => true]);
        $datos = fn () => [
            'cliente_id' => $cliente->id,
            'forma_pago' => 'efectivo',
            'detalles' => [['descripcion' => 'Item', 'cantidad' => 1, 'precio_unitario' => 10]],
        ];

        $this->actingAs($this->cajero)->post(route('ventas.store'), $datos());
        $this->actingAs($this->cajero)->post(route('ventas.store'), $datos());

        $numeros = Venta::orderBy('id')->pluck('numero_operacion');

        $this->assertCount(2, $numeros);
        $this->assertNotEquals($numeros[0], $numeros[1]);
    }

    public function test_venta_requires_at_least_one_item(): void
    {
        $cliente = Cliente::factory()->create(['estado' => true]);

        $this->actingAs($this->cajero)
            ->post(route('ventas.store'), [
                'cliente_id' => $cliente->id,
                'forma_pago' => 'efectivo',
                'detalles' => [],
            ])
            ->assertSessionHasErrors('detalles');
    }

    public function test_cannot_sell_to_an_inactive_cliente(): void
    {
        $cliente = Cliente::factory()->create(['estado' => false]);

        $this->actingAs($this->cajero)
            ->post(route('ventas.store'), [
                'cliente_id' => $cliente->id,
                'forma_pago' => 'efectivo',
                'detalles' => [['descripcion' => 'Item', 'cantidad' => 1, 'precio_unitario' => 10]],
            ])
            ->assertSessionHasErrors('cliente_id');
    }

    public function test_administrativo_can_anular_a_venta(): void
    {
        $venta = Venta::factory()->create(['estado' => 'registrada']);

        $this->actingAs($this->administrativo)
            ->patch(route('ventas.anular', $venta))
            ->assertRedirect(route('ventas.show', $venta));

        $this->assertDatabaseHas('ventas', ['id' => $venta->id, 'estado' => 'anulada']);
        $this->assertDatabaseHas('auditorias', ['accion' => 'venta.anulada', 'auditable_id' => $venta->id]);
    }

    public function test_cajero_cannot_anular_a_venta(): void
    {
        $venta = Venta::factory()->create(['estado' => 'registrada']);

        $this->actingAs($this->cajero)
            ->patch(route('ventas.anular', $venta))
            ->assertForbidden();
    }

    public function test_an_already_anulada_venta_cannot_be_anulada_again(): void
    {
        $venta = Venta::factory()->create(['estado' => 'anulada']);

        $this->actingAs($this->administrativo)
            ->patch(route('ventas.anular', $venta))
            ->assertRedirect();

        $this->assertDatabaseHas('ventas', ['id' => $venta->id, 'estado' => 'anulada']);
        $this->assertDatabaseMissing('auditorias', ['accion' => 'venta.anulada', 'auditable_id' => $venta->id]);
    }

    public function test_cannot_anular_a_venta_with_an_emitted_boleta(): void
    {
        $venta = Venta::factory()->create(['estado' => 'registrada']);
        Comprobante::factory()->create(['venta_id' => $venta->id, 'estado' => 'emitido']);

        $this->actingAs($this->administrativo)
            ->patch(route('ventas.anular', $venta))
            ->assertRedirect();

        $this->assertDatabaseHas('ventas', ['id' => $venta->id, 'estado' => 'registrada']);
    }

    public function test_cannot_anular_a_venta_with_an_active_cuenta_por_cobrar(): void
    {
        $venta = Venta::factory()->create(['estado' => 'registrada']);
        CuentaPorCobrar::factory()->create(['venta_id' => $venta->id, 'estado' => 'pendiente']);

        $this->actingAs($this->administrativo)
            ->patch(route('ventas.anular', $venta))
            ->assertRedirect();

        $this->assertDatabaseHas('ventas', ['id' => $venta->id, 'estado' => 'registrada']);
    }

    public function test_can_anular_a_venta_once_its_cuenta_por_cobrar_is_anulada(): void
    {
        $venta = Venta::factory()->create(['estado' => 'registrada']);
        CuentaPorCobrar::factory()->create(['venta_id' => $venta->id, 'estado' => 'anulada']);

        $this->actingAs($this->administrativo)
            ->patch(route('ventas.anular', $venta))
            ->assertRedirect(route('ventas.show', $venta));

        $this->assertDatabaseHas('ventas', ['id' => $venta->id, 'estado' => 'anulada']);
    }
}
