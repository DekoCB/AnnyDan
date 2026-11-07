<?php

namespace Tests\Feature;

use App\Models\Auditoria;
use App\Models\Cliente;
use App\Models\Comprobante;
use App\Models\CuentaPorCobrar;
use App\Models\CuentaPorPagar;
use App\Models\MovimientoCaja;
use App\Models\User;
use App\Models\Venta;
use Database\Seeders\CategoriasMovimientoSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Guarda de regresión N+1 — sección 19 del requerimiento ("no cargar
 * miles de registros innecesariamente", "consultas repetidas").
 *
 * No mide milisegundos (el tiempo depende de la máquina que ejecute el
 * test); mide algo estable entre entornos: el NÚMERO de consultas SQL
 * por página. Si alguien rompe un ->with(...) y reintroduce un N+1, el
 * conteo de consultas crece junto con el número de filas — estos tests
 * lo detectan aunque el dataset de prueba sea pequeño.
 *
 * Los límites están fijados con margen por encima de los conteos reales
 * medidos manualmente contra ~400 ventas / ~2000 auditorías (ver informe
 * final, sección de rendimiento) — no son un número arbitrario. Incluyen
 * las 2 consultas fijas del TopbarComposer (alerta de cuentas vencidas),
 * presente en cada página autenticada.
 */
class PerformanceTest extends TestCase
{
    use RefreshDatabase;

    private User $administrativo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(CategoriasMovimientoSeeder::class);

        $this->administrativo = User::factory()->create();
        $this->administrativo->assignRole('ADMINISTRATIVO');
    }

    private function contarQueries(string $ruta): int
    {
        DB::flushQueryLog();
        DB::enableQueryLog();

        $this->actingAs($this->administrativo)->get($ruta)->assertOk();

        return count(DB::getQueryLog());
    }

    public function test_clientes_index_query_count_does_not_scale_with_row_count(): void
    {
        Cliente::factory()->count(30)->create();

        $this->assertLessThan(10, $this->contarQueries(route('clientes.index')));
    }

    public function test_ventas_index_query_count_does_not_scale_with_row_count(): void
    {
        Venta::factory()->count(30)->create();

        $this->assertLessThan(10, $this->contarQueries(route('ventas.index')));
    }

    public function test_cuentas_por_cobrar_index_query_count_does_not_scale_with_row_count(): void
    {
        CuentaPorCobrar::factory()->count(30)->create();

        $this->assertLessThan(12, $this->contarQueries(route('cuentas-cobrar.index')));
    }

    public function test_cuentas_por_pagar_index_query_count_does_not_scale_with_row_count(): void
    {
        CuentaPorPagar::factory()->count(30)->create();

        $this->assertLessThan(12, $this->contarQueries(route('cuentas-pagar.index')));
    }

    public function test_caja_index_query_count_does_not_scale_with_row_count(): void
    {
        MovimientoCaja::factory()->count(30)->create(['fecha' => now()]);

        $this->assertLessThan(14, $this->contarQueries(route('caja.index')));
    }

    public function test_comprobantes_index_query_count_does_not_scale_with_row_count(): void
    {
        Comprobante::factory()->count(30)->create();

        $this->assertLessThan(12, $this->contarQueries(route('comprobantes.index')));
    }

    public function test_auditoria_index_query_count_does_not_scale_with_row_count(): void
    {
        $contabilidad = User::factory()->create();
        $contabilidad->assignRole('CONTABILIDAD');

        Auditoria::factory()->count(60)->create();

        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->actingAs($contabilidad)->get(route('auditoria.index'))->assertOk();

        $this->assertLessThan(12, count(DB::getQueryLog()));
    }

    public function test_dashboard_query_count_stays_bounded_regardless_of_data_volume(): void
    {
        Venta::factory()->count(30)->create(['fecha' => now(), 'estado' => 'registrada']);
        CuentaPorCobrar::factory()->count(20)->create();
        CuentaPorPagar::factory()->count(20)->create();
        MovimientoCaja::factory()->count(20)->create(['fecha' => now()]);

        // El dashboard agrega varios módulos a la vez (ventas, CxC, CxP,
        // caja, últimas ventas, pagos recientes) — su cuenta de consultas
        // es naturalmente mayor que la de un índice simple, pero sigue
        // siendo un número FIJO de agregados, no uno por fila.
        $this->assertLessThan(40, $this->contarQueries(route('dashboard')));
    }
}
