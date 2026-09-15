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

/**
 * Prueba de regresión de extremo a extremo: encadena los módulos de las
 * Fases 4 a 10 (cliente → venta → cuenta por cobrar → pago → caja →
 * boleta) en un solo flujo, tal como lo usaría un cajero real. El
 * objetivo es detectar si un cambio futuro en un módulo rompe su
 * integración con los demás, no solo su comportamiento aislado.
 */
class BusinessFlowRegressionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(CategoriasMovimientoSeeder::class);
    }

    public function test_full_sale_to_cash_and_boleta_pipeline_stays_consistent(): void
    {
        $cajero = User::factory()->create();
        $cajero->assignRole('CAJERO');

        $administrativo = User::factory()->create();
        $administrativo->assignRole('ADMINISTRATIVO');

        // 1. Cliente
        $this->actingAs($administrativo)->post(route('clientes.store'), [
            'tipo_documento' => 'DNI',
            'numero_documento' => '40404040',
            'nombre_razon_social' => 'Cliente Flujo Completo',
        ])->assertRedirect();

        $cliente = Cliente::firstWhere('numero_documento', '40404040');
        $this->assertNotNull($cliente);

        // 2. Venta con dos ítems — el total lo calcula el backend.
        $this->actingAs($cajero)->post(route('ventas.store'), [
            'cliente_id' => $cliente->id,
            'forma_pago' => 'efectivo',
            'detalles' => [
                ['descripcion' => 'Producto A', 'cantidad' => 3, 'precio_unitario' => 40],
                ['descripcion' => 'Producto B', 'cantidad' => 1, 'precio_unitario' => 20],
            ],
        ])->assertRedirect();

        $venta = Venta::where('cliente_id', $cliente->id)->first();
        $this->assertSame('140.00', $venta->subtotal); // 3*40 + 1*20
        $this->assertSame('registrada', $venta->estado);

        // 3. Cuenta por cobrar generada desde la venta (monto tomado del servidor).
        $this->actingAs($administrativo)->post(route('cuentas-cobrar.store'), [
            'venta_id' => $venta->id,
            'cliente_id' => $cliente->id,
            'monto_total' => 1, // intento de manipular — debe ignorarse
            'fecha_vencimiento' => now()->addDays(15)->toDateString(),
        ])->assertRedirect();

        $cuenta = CuentaPorCobrar::where('venta_id', $venta->id)->first();
        $this->assertSame($venta->total, $cuenta->monto_total);
        $this->assertSame('pendiente', $cuenta->estado);

        // 4. Pago total registrado por el cajero.
        $this->actingAs($cajero)->post(route('cuentas-cobrar.pagar', $cuenta), [
            'monto' => $cuenta->monto_total,
            'fecha_pago' => now()->toDateString(),
            'medio_pago' => 'efectivo',
        ])->assertRedirect();

        $cuenta->refresh();
        $this->assertSame('pagada', $cuenta->estado);
        $this->assertSame('0.00', $cuenta->saldo);

        // 5. El pago debe haber generado un movimiento de caja de ingreso — nunca la venta por sí sola.
        $this->assertDatabaseHas('movimientos_caja', [
            'tipo' => 'ingreso',
            'monto' => $venta->total,
        ]);

        // 6. Boleta emitida para la venta.
        $this->actingAs($cajero)->post(route('comprobantes.store', $venta))->assertRedirect();

        $venta->refresh();
        $this->assertNotNull($venta->comprobante);
        $this->assertSame('emitido', $venta->comprobante->estado);
        $this->assertSame('B001-00000001', $venta->comprobante->numero_completo);

        // 7. Con boleta emitida y cuenta pagada, la venta ya no puede anularse.
        $this->actingAs($administrativo)->patch(route('ventas.anular', $venta))->assertRedirect();
        $venta->refresh();
        $this->assertSame('registrada', $venta->estado);

        // 8. Cada paso del flujo dejó su propia huella de auditoría.
        foreach ([
            'cliente.creado', 'venta.creada', 'cuenta_por_cobrar.creada',
            'pago_cliente.registrado', 'comprobante.emitido',
        ] as $accion) {
            $this->assertDatabaseHas('auditorias', ['accion' => $accion]);
        }
    }
}
