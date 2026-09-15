<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\User;
use App\Models\Venta;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Suite consolidada de seguridad — sección 26 del requerimiento:
 * acceso sin autenticación, acceso con rol incorrecto, manipulación de
 * IDs, entradas maliciosas y acceso a información según permisos. Los
 * módulos individuales ya prueban sus propias reglas de autorización;
 * esta suite verifica las garantías transversales que aplican a toda
 * la aplicación.
 */
class SecurityTest extends TestCase
{
    use RefreshDatabase;

    private User $administrativo;

    private User $cajero;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->administrativo = User::factory()->create();
        $this->administrativo->assignRole('ADMINISTRATIVO');

        $this->cajero = User::factory()->create();
        $this->cajero->assignRole('CAJERO');
    }

    /**
     * 1. Acceso sin autenticación: ninguna pantalla de negocio debe ser
     * alcanzable sin sesión.
     */
    public function test_every_protected_route_redirects_unauthenticated_users_to_login(): void
    {
        $rutas = [
            'dashboard', 'clientes.index', 'proveedores.index', 'ventas.index',
            'cuentas-cobrar.index', 'cuentas-pagar.index', 'caja.index',
            'reportes.ventas', 'comprobantes.index', 'auditoria.index',
        ];

        foreach ($rutas as $nombre) {
            $this->get(route($nombre))->assertRedirect(route('login'));
        }
    }

    /**
     * 2. Acceso con rol incorrecto: un usuario autenticado sin el permiso
     * necesario no puede ejecutar la acción llamando al endpoint
     * directamente, aunque no vea el botón en la UI.
     */
    public function test_cajero_cannot_bypass_the_ui_by_calling_privileged_endpoints_directly(): void
    {
        $cliente = Cliente::factory()->create();

        // CAJERO no tiene clientes.crear, cuentas_pagar.*, reportes.ver ni auditoria.ver.
        $this->actingAs($this->cajero)->post(route('clientes.store'), [
            'tipo_documento' => 'DNI', 'numero_documento' => '87654321', 'nombre_razon_social' => 'Intento',
        ])->assertForbidden();

        $this->actingAs($this->cajero)->delete(route('clientes.destroy', $cliente))->assertForbidden();
        $this->actingAs($this->cajero)->get(route('cuentas-pagar.create'))->assertForbidden();
        $this->actingAs($this->cajero)->get(route('reportes.ventas'))->assertForbidden();
        $this->actingAs($this->cajero)->get(route('auditoria.index'))->assertForbidden();
    }

    /**
     * 3. Manipulación de IDs: un ID válido pero inexistente debe dar 404,
     * nunca un error de servidor ni una fuga de datos.
     */
    public function test_requesting_a_nonexistent_id_returns_404_across_modules(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('ADMINISTRATIVO');

        $this->actingAs($admin)->get(route('clientes.show', 999999))->assertNotFound();
        $this->actingAs($admin)->get(route('proveedores.show', 999999))->assertNotFound();
        $this->actingAs($admin)->get(route('ventas.show', 999999))->assertNotFound();
        $this->actingAs($admin)->get(route('cuentas-cobrar.show', 999999))->assertNotFound();
        $this->actingAs($admin)->get(route('cuentas-pagar.show', 999999))->assertNotFound();
        $this->actingAs($admin)->get(route('comprobantes.show', 999999))->assertNotFound();
    }

    /**
     * 4a. Entradas maliciosas (XSS): un payload de script almacenado debe
     * salir siempre escapado — Blade escapa por defecto con {{ }}, esta
     * prueba confirma que ningún componente lo está imprimiendo sin
     * escapar.
     */
    public function test_xss_payload_in_cliente_name_is_rendered_escaped_not_executed(): void
    {
        $payload = '<script>alert(1)</script>';

        $this->actingAs($this->administrativo)->post(route('clientes.store'), [
            'tipo_documento' => 'DNI',
            'numero_documento' => '11223344',
            'nombre_razon_social' => $payload,
        ]);

        $cliente = Cliente::firstWhere('numero_documento', '11223344');

        $response = $this->actingAs($this->administrativo)->get(route('clientes.show', $cliente));

        $response->assertOk();
        $response->assertDontSee($payload, false);
        $response->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false);
    }

    /**
     * 4b. Entradas maliciosas (inyección SQL): un intento clásico de
     * inyección en un campo de búsqueda debe tratarse como texto literal
     * (Eloquent parametriza los bindings) y no debe romper la consulta.
     */
    public function test_sql_injection_style_input_in_search_does_not_break_the_query(): void
    {
        Cliente::factory()->create(['nombre_razon_social' => 'Cliente Normal']);

        $response = $this->actingAs($this->administrativo)->get(route('clientes.index', [
            'q' => "' OR '1'='1'; DROP TABLE clientes; --",
        ]));

        $response->assertOk();
        $this->assertDatabaseCount('clientes', 1);
    }

    /**
     * 4c. Entradas maliciosas (mass assignment): campos no expuestos en
     * el Form Request (como `estado`) deben ser ignorados aunque el
     * cliente los envíe explícitamente en el POST.
     */
    public function test_unvalidated_fields_cannot_be_mass_assigned_through_the_request(): void
    {
        $this->actingAs($this->administrativo)->post(route('clientes.store'), [
            'tipo_documento' => 'DNI',
            'numero_documento' => '55667788',
            'nombre_razon_social' => 'Cliente Intento Escalado',
            'estado' => false,
        ]);

        $this->assertDatabaseHas('clientes', [
            'numero_documento' => '55667788',
            'estado' => true,
        ]);
    }

    /**
     * 5. Acceso a información según permisos: CONTABILIDAD tiene
     * visibilidad de solo lectura sobre los módulos financieros, nunca
     * capacidad de escritura, aunque conozca IDs válidos.
     */
    public function test_contabilidad_has_read_only_access_to_financial_modules(): void
    {
        $contabilidad = User::factory()->create();
        $contabilidad->assignRole('CONTABILIDAD');

        $venta = Venta::factory()->create();

        $this->actingAs($contabilidad)->get(route('ventas.index'))->assertOk();
        $this->actingAs($contabilidad)->get(route('ventas.show', $venta))->assertOk();

        $this->actingAs($contabilidad)->post(route('ventas.store'), [])->assertForbidden();
        $this->actingAs($contabilidad)->patch(route('ventas.anular', $venta))->assertForbidden();
    }
}
